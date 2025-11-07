import serial
import threading
import mysql.connector
from datetime import datetime
import time
import logging

# ================== CONFIGURATION ==================
CONFIG = {
    'database': {
        'host': 'localhost',
        'user': 'root', 
        'password': '',
        'database': 'facility_control_v3'
    },
    'debounce_time': 3  # seconds between same card reads
}

# ================== LOGGING SETUP ==================
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('access_control.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger('AccessControl')

# ================== DATABASE CLASS ==================
class Database:
    def __init__(self):
        self.conn = mysql.connector.connect(**CONFIG['database'])
        self.cursor = self.conn.cursor(dictionary=True)

    def query(self, sql, params=None, fetchone=False):
        self.cursor.execute(sql, params or ())
        if fetchone:
            return self.cursor.fetchone()
        return self.cursor.fetchall()

    def execute(self, sql, params=None):
        self.cursor.execute(sql, params or ())
        self.conn.commit()
        return self.cursor.rowcount

    def close(self):
        self.cursor.close()
        self.conn.close()

# ================== ACCESS CONTROL CLASS ==================
class AccessControl:
    def __init__(self, db):
        self.db = db

    def check_access(self, uid, room_id, is_exit=False):
        # Check if user exists and is active
        user = self.db.query("""
            SELECT User_id, Role, Status 
            FROM users WHERE Rfid_tag=%s AND Status='Active'
        """, (uid,), fetchone=True)

        if not user:
            return None, "denied", "Unknown user", "unknown"

        user_id = user["User_id"]
        role = user["Role"].lower()
        
        # For exit, always allow if room is occupied
        if is_exit:
            return user_id, "granted", "Exit granted", role
        
        # For entry - check schedule access
        now = datetime.now()
        current_day = now.strftime("%a")
        current_time = now.strftime("%H:%M:%S")

        # Check if user has access to this room at current time
        schedule = self.db.query("""
            SELECT s.Schedule_id 
            FROM schedule s
            JOIN schedule_access sa ON s.Schedule_id=sa.Schedule_id
            JOIN users u ON sa.CourseSection_id=u.CourseSection_id
            WHERE u.User_id=%s AND s.Room_id=%s AND s.Day=%s
            AND %s BETWEEN s.Start_time AND s.End_time
        """, (user_id, room_id, current_day, current_time), fetchone=True)

        if schedule or role == "admin":
            return user_id, "granted", "Access granted", role
        else:
            return user_id, "denied", "No schedule access", role

# ================== RFID READER CLASS ==================
class RFIDReader(threading.Thread):
    def __init__(self, port, room_id, db, access_ctrl):
        super().__init__(daemon=True)
        self.port = port
        self.room_id = room_id
        self.db = db
        self.access_ctrl = access_ctrl
        self.running = True
        
        # Debouncing variables - separate for reading and logging
        self.last_read_uid = None
        self.last_read_time = 0
        self.last_log_uid = None
        self.last_log_time = 0

    def send_command(self, ser, cmd):
        """Send simple command to Arduino"""
        try:
            ser.write(f"{cmd}\n".encode())
            logger.debug(f"Room {self.room_id} -> Arduino: {cmd}")
        except Exception as e:
            logger.error(f"Room {self.room_id} send failed: {e}")

    def log_access(self, user_id, uid, access_type, status, reason=""):
        """Log access attempt with duplicate prevention"""
        current_time = time.time()
        
        # Prevent duplicate logs for same UID within 2 seconds
        log_key = f"{uid}_{access_type}_{status}"
        if log_key == self.last_log_uid and current_time - self.last_log_time < 2:
            logger.debug(f"Skipped duplicate log for UID {uid}")
            return
            
        self.last_log_uid = log_key
        self.last_log_time = current_time
        
        try:
            self.db.execute("""
                INSERT INTO access_log (User_id, Rfid_tag, Room_id, Access_time, Access_type, Status, Reason)
                VALUES (%s, %s, %s, NOW(), %s, %s, %s)
            """, (user_id, uid, self.room_id, access_type, status, reason))
            logger.info(f"Access: {access_type}, UID {uid}, Room {self.room_id}, {status}")
        except Exception as e:
            logger.error(f"Failed to log access: {e}")

    def get_room_status(self):
        """Get current room status from database"""
        try:
            room_data = self.db.query(
                "SELECT Status FROM classrooms WHERE Room_id=%s", 
                (self.room_id,), fetchone=True
            )
            return room_data["Status"] if room_data else "Unoccupied"
        except Exception as e:
            logger.error(f"Failed to get room status: {e}")
            return "Unoccupied"

    def update_room_status(self, status):
        """Update room status in database"""
        try:
            self.db.execute(
                "UPDATE classrooms SET Status=%s WHERE Room_id=%s", 
                (status, self.room_id)
            )
            logger.debug(f"Room {self.room_id} status updated to {status}")
        except Exception as e:
            logger.error(f"Failed to update room status: {e}")

    def process_access(self, uid, ser):
        """Process RFID access attempt"""
        current_room_status = self.get_room_status()
        is_exit = current_room_status == "Occupied"
        
        logger.info(f"Room {self.room_id} processing {'EXIT' if is_exit else 'ENTRY'} for UID: {uid}, Current status: {current_room_status}")
        
        user_id, status, reason, role = self.access_ctrl.check_access(uid, self.room_id, is_exit)

        if status == "granted":
            if is_exit:
                # Exit logic
                if role in ["admin", "faculty"]:
                    # Faculty/Admin can turn off everything
                    self.send_command(ser, "EXIT_OFF")
                    self.update_room_status("Unoccupied")
                    self.log_access(user_id, uid, 'Exit', 'granted', f"{role} turned off room")
                    logger.info(f"Room {self.room_id} {role} exited and turned off room")
                else:
                    # Student can only unlock door for exit (room stays occupied)
                    self.send_command(ser, "EXIT_UNLOCK")
                    self.log_access(user_id, uid, 'Exit', 'granted', "Student exit - door unlocked")
                    logger.info(f"Room {self.room_id} student exited - door unlocked")
            else:
                # Entry logic - always turn on light and unlock door
                self.send_command(ser, "ENTRY_GRANT")
                self.update_room_status("Occupied")
                self.log_access(user_id, uid, 'Entry', 'granted', reason)
                logger.info(f"Room {self.room_id} access GRANTED")
        else:
            # Deny access
            self.send_command(ser, "DENY")
            access_type = 'Exit' if is_exit else 'Entry'
            self.log_access(user_id, uid, access_type, 'denied', reason)
            logger.info(f"Room {self.room_id} {access_type} DENIED")

    def run(self):
        """Main reader loop"""
        logger.info(f"Starting RFID reader for Room {self.room_id} on {self.port}")
        
        try:
            ser = serial.Serial(self.port, 9600, timeout=1)
            time.sleep(2)  # Arduino reset time
            
            while self.running:
                if ser.in_waiting > 0:
                    try:
                        line = ser.readline().decode('utf-8', errors='ignore').strip()
                        
                        if not line:
                            continue
                            
                        # Process UID
                        if line.startswith("UID:"):
                            uid = line[4:].strip()
                            current_time = time.time()
                            
                            # Basic UID validation
                            if not uid or len(uid) < 2:
                                continue
                            
                            # Hardware debouncing - ignore same card within debounce time
                            if (uid == self.last_read_uid and 
                                current_time - self.last_read_time < CONFIG['debounce_time']):
                                logger.debug(f"Room {self.room_id} debounced UID: {uid}")
                                continue
                            
                            self.last_read_uid = uid
                            self.last_read_time = current_time
                            
                            logger.debug(f"Room {self.room_id} processing UID: {uid}")
                            self.process_access(uid, ser)
                        
                    except Exception as e:
                        logger.error(f"Room {self.room_id} read error: {e}")
                
                time.sleep(0.1)  # Prevent CPU overload
            
        except Exception as e:
            logger.error(f"Room {self.room_id} fatal error: {e}")
        finally:
            logger.info(f"Room {self.room_id} reader stopped")

    def stop(self):
        self.running = False

# ================== AUTO-OFF MONITOR ==================
class AutoOffMonitor(threading.Thread):
    def __init__(self, db, check_interval=60):
        super().__init__(daemon=True)
        self.db = db
        self.interval = check_interval
        self.running = True

    def run(self):
        logger.info("Auto-off monitor started")
        while self.running:
            try:
                now = datetime.now()
                current_time = now.strftime("%H:%M:%S")
                current_day = now.strftime("%a")
                
                # Find rooms that should be turned off (schedule ended)
                rooms_to_off = self.db.query("""
                    SELECT DISTINCT c.Room_id 
                    FROM classrooms c
                    JOIN schedule s ON c.Room_id = s.Room_id
                    WHERE c.Status='Occupied'
                    AND s.End_time < %s AND s.Day=%s
                """, (current_time, current_day))
                
                for room in rooms_to_off:
                    logger.info(f"Auto-off: Room {room['Room_id']} schedule ended, turning off")
                    self.db.execute(
                        "UPDATE classrooms SET Status='Unoccupied' WHERE Room_id=%s",
                        (room["Room_id"],)
                    )
                    logger.info(f"Auto-off: Room {room['Room_id']} set to Unoccupied")
                
                time.sleep(self.interval)
            except Exception as e:
                logger.error(f"Auto-off monitor error: {e}")
                time.sleep(self.interval)

    def stop(self):
        self.running = False

# ================== RFID MANAGER CLASS ==================
class RFIDManager:
    def __init__(self, db):
        self.db = db
        self.access_ctrl = AccessControl(db)
        self.readers = []
        self.auto_off = None

    def load_readers(self):
        """Load and start all active RFID readers"""
        readers = self.db.query(
            "SELECT Room_id, Port_name FROM rfid_reader WHERE Status='Active'"
        )
        
        if not readers:
            logger.warning("No active RFID readers found")
            return
        
        for reader_config in readers:
            try:
                reader = RFIDReader(
                    reader_config["Port_name"],
                    reader_config["Room_id"],
                    self.db,
                    self.access_ctrl
                )
                self.readers.append(reader)
                reader.start()
                logger.info(f"Started reader for Room {reader_config['Room_id']} on {reader_config['Port_name']}")
            except Exception as e:
                logger.error(f"Failed to start reader for Room {reader_config['Room_id']}: {e}")

    def run(self):
        """Main application loop"""
        logger.info("Starting RFID Access Control System")
        
        try:
            self.load_readers()
            self.auto_off = AutoOffMonitor(self.db)
            self.auto_off.start()
            
            # Keep main thread alive
            while True:
                time.sleep(1)
                
        except KeyboardInterrupt:
            logger.info("Shutdown signal received")
        except Exception as e:
            logger.error(f"Manager fatal error: {e}")
        finally:
            self.shutdown()

    def shutdown(self):
        """Graceful shutdown"""
        logger.info("Shutting down system...")
        
        if self.auto_off:
            self.auto_off.stop()
        
        for reader in self.readers:
            reader.stop()
        
        time.sleep(2)
        logger.info("System stopped")

# ================== MAIN ==================
if __name__ == "__main__":
    try:
        db = Database()
        manager = RFIDManager(db)
        manager.run()
    except Exception as e:
        logger.error(f"Application failed to start: {e}")