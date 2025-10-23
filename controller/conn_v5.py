import serial
import threading
import mysql.connector
from datetime import datetime
import time

# ================== DATABASE CLASS ==================
class Database:
    def __init__(self, host="localhost", user="root", password="", database="facility_control_v3"):
        self.conn = mysql.connector.connect(
            host=host, user=user, password=password, database=database
        )
        self.cursor = self.conn.cursor(dictionary=True)

    def query(self, sql, params=None, fetchone=False):
        self.cursor.execute(sql, params or ())
        return self.cursor.fetchone() if fetchone else self.cursor.fetchall()

    def execute(self, sql, params=None, commit=True):
        self.cursor.execute(sql, params or ())
        if commit:
            self.conn.commit()

# ================== ACCESS CONTROL CLASS ==================
class AccessControl:
    def __init__(self, db: Database):
        self.db = db

    def check_access(self, uid, room_id):
        now = datetime.now()
        current_day = now.strftime("%a")  # Mon, Tue, ...
        current_time = now.strftime("%H:%M:%S")

        # 1. Find user
        user = self.db.query("""
            SELECT User_id, Status, CourseSection_id, Role
            FROM users
            WHERE Rfid_tag = %s
        """, (uid,), fetchone=True)

        if not user:
            return None, None, "denied"

        if user["Status"] != "Active":
            return user["User_id"], None, "denied"

        user_id = user["User_id"]
        role = user["Role"].lower()

        # Admin can always access
        if role == "admin":
            return user_id, None, "granted"

        # Faculty access check
        if role == "faculty":
            schedule = self.db.query("""
                SELECT Schedule_id, Room_id, End_time
                FROM schedule
                WHERE Faculty_id = %s
                AND Room_id = %s
                AND Day = %s
                AND %s BETWEEN Start_time AND End_time
            """, (user_id, room_id, current_day, current_time), fetchone=True)
        else:  # Student access check
            schedule = self.db.query("""
                SELECT s.Schedule_id, s.Room_id, s.End_time
                FROM schedule s
                JOIN schedule_access sa ON sa.Schedule_id = s.Schedule_id
                WHERE sa.CourseSection_id = %s
                AND s.Room_id = %s
                AND s.Day = %s
                AND %s BETWEEN s.Start_time AND s.End_time
            """, (user["CourseSection_id"], room_id, current_day, current_time), fetchone=True)

        if schedule:
            return user_id, schedule["Schedule_id"], "granted"
        return user_id, None, "denied"

# ================== RFID READER CLASS ==================
class RFIDReader(threading.Thread):
    def __init__(self, port, room_id, db, access_ctrl):
        super().__init__(daemon=True)
        self.port = port
        self.room_id = room_id
        self.db = db
        self.access_ctrl = access_ctrl
        self.running = True           # graceful stop flag
        self.last_uid = None
        self.last_scan_time = 0

    # -----------------------------
    # Log an access event
    # -----------------------------
    def log_access(self, user_id, uid, schedule_id, access_type, status):
        self.db.execute("""
            INSERT INTO access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (user_id, uid, self.room_id, schedule_id, datetime.now(), access_type, status))
        self.db.commit()  # ensure changes are saved

    # -----------------------------
    # Send command to Arduino
    # -----------------------------
    def send_command(self, ser, command):
        try:
            ser.write(f"{command}\n".encode())
        except Exception as e:
            print(f"[Room {self.room_id}] ⚠️ Failed to send command '{command}': {e}")

    # -----------------------------
    # Main RFID thread loop
    # -----------------------------
    def run(self):
        while self.running:
            try:
                ser = serial.Serial(self.port, 9600, timeout=1)
                time.sleep(2)
                print(f"✅ RFID Reader ready on {self.port} (Room {self.room_id})")

                while self.running:
                    if ser.in_waiting > 0:
                        uid = ser.readline().decode('utf-8').strip()
                        now = time.time()

                        # Skip empty or repeated scans within 3s
                        if not uid or (uid == self.last_uid and now - self.last_scan_time < 3):
                            continue

                        self.last_uid = uid
                        self.last_scan_time = now

                        print(f"[Room {self.room_id}] UID scanned: {uid}")

                        result = self.access_ctrl.check_access(uid, self.room_id)
                        if not result:
                            print(f"[Room {self.room_id}] ❌ Unknown or unauthorized UID: {uid}")
                            self.send_command(ser, 'DENIED')
                            continue

                        user_id, schedule_id, access_status = result
                        if access_status not in ['granted', 'denied']:
                            access_status = 'denied'

                        # Get user role
                        user = self.db.query(
                            "SELECT Role FROM users WHERE Rfid_tag = %s", (uid,), fetchone=True)
                        role = user["Role"].lower() if user else ""

                        # Get current room status
                        room = self.db.query(
                            "SELECT Status FROM classrooms WHERE Room_id = %s", (self.room_id,), fetchone=True)
                        room_status = room["Status"] if room else "Unoccupied"

                        # -------------------------------
                        # ADMIN ENTRY/EXIT LOGIC
                        # -------------------------------
                        if role == "admin":
                            if room_status == "Unoccupied":
                                self.log_access(user_id, uid, None, 'Entry', 'granted')
                                self.db.execute(
                                    "UPDATE classrooms SET Status = 'Occupied' WHERE Room_id = %s",
                                    (self.room_id,))
                                self.db.commit()
                                self.send_command(ser, 'ROOM_ON')
                                print(f"[Room {self.room_id}] 🟢 Admin entered → Room ON")
                            else:
                                self.log_access(user_id, uid, None, 'Exit', 'granted')
                                self.db.execute(
                                    "UPDATE classrooms SET Status = 'Unoccupied' WHERE Room_id = %s",
                                    (self.room_id,))
                                self.db.commit()
                                self.send_command(ser, 'ROOM_OFF')
                                print(f"[Room {self.room_id}] 🔴 Admin exited → Room OFF")
                            continue

                        # -------------------------------
                        # FACULTY EXIT LOGIC
                        # -------------------------------
                        if role == "faculty" and room_status == "Occupied":
                            # Optional: check if other users are still inside
                            active_count = self.db.query(
                                "SELECT COUNT(*) AS total FROM access_log WHERE Room_id = %s AND Access_type='Entry' AND Status='granted' AND DATE(Access_time)=CURDATE()",
                                (self.room_id,), fetchone=True)
                            if active_count and active_count["total"] > 1:
                                print(f"[Room {self.room_id}] Faculty exit ignored → others still inside")
                                continue

                            self.log_access(user_id, uid, None, 'Exit', 'granted')
                            self.db.execute(
                                "UPDATE classrooms SET Status = 'Unoccupied' WHERE Room_id = %s",
                                (self.room_id,))
                            self.db.commit()
                            self.send_command(ser, 'ROOM_OFF')
                            print(f"[Room {self.room_id}] 🟡 Faculty exited → Room OFF")
                            continue

                        # -------------------------------
                        # NORMAL ENTRY LOGIC
                        # -------------------------------
                        self.log_access(user_id, uid, schedule_id, 'Entry', access_status)

                        if access_status == "granted":
                            self.db.execute(
                                "UPDATE classrooms SET Status = 'Occupied' WHERE Room_id = %s",
                                (self.room_id,))
                            self.db.commit()
                            self.send_command(ser, 'GRANTED')
                            print(f"[Room {self.room_id}] Access Granted ✅")
                        else:
                            self.send_command(ser, 'DENIED')
                            print(f"[Room {self.room_id}] Access Denied ❌")

            except serial.SerialException as e:
                print(f"❌ Serial error on {self.port}: {e}")
                time.sleep(5)  # retry after delay
            except Exception as e:
                print(f"⚠️ Unexpected error in RFIDReader (Room {self.room_id}): {e}")
                time.sleep(3)

    # -----------------------------
    # Graceful stop method
    # -----------------------------
    def stop(self):
        print(f"🛑 Stopping RFIDReader for Room {self.room_id}")
        self.running = False

# ================== AUTO TURN-OFF MONITOR ==================
class AutoOffMonitor(threading.Thread):
    def __init__(self, db: Database):
        super().__init__(daemon=True)
        self.db = db

    def run(self):
        while True:
            now = datetime.now()
            current_day = now.strftime("%a")
            current_time = now.strftime("%H:%M:%S")

            # Find schedules that just ended
            ended_schedules = self.db.query("""
                SELECT DISTINCT Room_id
                FROM schedule
                WHERE Day = %s AND End_time < %s
            """, (current_day, current_time))

            for s in ended_schedules:
                room_id = s["Room_id"]
                room = self.db.query("SELECT Status FROM classrooms WHERE Room_id = %s", (room_id,), fetchone=True)
                if room and room["Status"] == "Occupied":
                    self.db.execute("UPDATE classrooms SET Status = 'Unoccupied' WHERE Room_id = %s", (room_id,))
                    print(f"[AutoOff] Room {room_id} schedule ended → Turning OFF")

            time.sleep(60)  # check every minute

# ================== RFID MANAGER CLASS ==================
class RFIDManager:
    def __init__(self, db: Database):
        self.db = db
        self.access_ctrl = AccessControl(db)
        self.readers = []

    def load_readers(self):
        readers = self.db.query("SELECT Room_id, Port_name FROM rfid_reader WHERE Status = 'Active'")
        if not readers:
            print("⚠️ No active RFID readers found.")
            return

        for r in readers:
            reader = RFIDReader(r["Port_name"], r["Room_id"], self.db, self.access_ctrl)
            self.readers.append(reader)
            reader.start()
            print(f"📡 Started reader for Room {r['Room_id']} on {r['Port_name']}")

    def run(self):
        print("🚀 Starting RFID Access Service...")
        self.load_readers()

        # Start auto-off monitor
        auto_off = AutoOffMonitor(self.db)
        auto_off.start()

        print("✅ System running and monitoring all readers...")
        try:
            while True:
                time.sleep(1)
        except KeyboardInterrupt:
            print("🛑 Stopping service...")

# ================== MAIN ==================
if __name__ == "__main__":
    db = Database()
    manager = RFIDManager(db)
    manager.run()
