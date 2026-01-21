import serial
import threading
import mysql.connector
from datetime import datetime, timedelta
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
        if fetchone:
            return self.cursor.fetchone()
        return self.cursor.fetchall()

    def execute(self, sql, params=None):
        self.cursor.execute(sql, params or ())
        self.conn.commit()

    def commit(self):
        self.conn.commit()

    def close(self):
        self.cursor.close()
        self.conn.close()

# ================== ACCESS CONTROL CLASS ==================
class AccessControl:
    def __init__(self, db: Database):
        self.db = db
        self.active_sessions = {}  # {room_id: {"users": set(), "end_time": datetime}}

    def check_access(self, uid, room_id):
        now = datetime.now()
        current_day = now.strftime("%a")
        current_time = now.strftime("%H:%M:%S")

        user = self.db.query("""
            SELECT User_id, Role, Status, CourseSection_id 
            FROM users WHERE Rfid_tag=%s
        """, (uid,), fetchone=True)

        if not user:
            return None, None, "denied", "Unknown user"

        if user["Status"] != "Active":
            return user["User_id"], None, "denied", "Inactive user"

        role = user["Role"].lower()
        user_id = user["User_id"]

        if role == "admin":
            return user_id, None, "granted", "Admin override"

        # Faculty
        if role == "faculty":
            schedule = self.db.query("""
                SELECT Schedule_id, End_time
                FROM schedule
                WHERE Faculty_id=%s AND Room_id=%s AND Day=%s
                AND %s BETWEEN Start_time AND End_time
            """, (user_id, room_id, current_day, current_time), fetchone=True)
        else:
            # Student
            schedule = self.db.query("""
                SELECT s.Schedule_id, s.End_time
                FROM schedule s
                JOIN schedule_access sa ON s.Schedule_id=sa.Schedule_id
                WHERE sa.CourseSection_id=%s AND s.Room_id=%s
                AND s.Day=%s AND %s BETWEEN s.Start_time AND s.End_time
            """, (user["CourseSection_id"], room_id, current_day, current_time), fetchone=True)

        if schedule:
            return user_id, schedule["Schedule_id"], "granted", "Within schedule"
        else:
            return user_id, None, "denied", "Outside schedule"

# ================== AUTO-OFF MONITOR CLASS ==================
class AutoOffMonitor(threading.Thread):
    def __init__(self, db: Database, check_interval=30):
        super().__init__(daemon=True)
        self.db = db
        self.interval = check_interval

    def run(self):
        while True:
            now = datetime.now().strftime("%H:%M:%S")
            day = datetime.now().strftime("%a")

            ended_rooms = self.db.query("""
                SELECT c.Room_id FROM classrooms c
                JOIN schedule s ON c.Room_id = s.Room_id
                WHERE c.Status='Occupied'
                AND s.End_time < %s AND s.Day=%s
            """, (now, day))

            for r in ended_rooms:
                self.db.execute("UPDATE classrooms SET Status='Unoccupied' WHERE Room_id=%s", (r["Room_id"],))
                print(f"[AutoOff] Room {r['Room_id']} → auto set to Unoccupied")

            time.sleep(self.interval)

# ================== RFID READER CLASS ==================
class RFIDReader(threading.Thread):
    def __init__(self, port, room_id, db: Database, access_ctrl: AccessControl):
        super().__init__(daemon=True)
        self.port = port
        self.room_id = room_id
        self.db = db
        self.access_ctrl = access_ctrl
        self.running = True
        self.last_uid = None
        self.last_scan_time = 0

    def send_command(self, ser, cmd):
        try:
            ser.write(f"{cmd}\n".encode())
        except Exception as e:
            print(f"[Room {self.room_id}] Send failed: {e}")

    def log_access(self, user_id, uid, schedule_id, access_type, status):
        self.db.execute("""
            INSERT INTO access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
            VALUES (%s,%s,%s,%s,NOW(),%s,%s)
        """, (user_id, uid, self.room_id, schedule_id, access_type, status))
        self.db.commit()

    def run(self):
        try:
            ser = serial.Serial(self.port, 9600, timeout=1)
            time.sleep(2)

            while self.running:
                if ser.in_waiting > 0:
                    uid = ser.readline().decode().strip()
                    now = time.time()

                    if not uid or uid.upper() == "READY" or not any(c.isalnum() for c in uid):
                        continue

                    if uid == self.last_uid and now - self.last_scan_time < 5:
                        continue

                    self.last_uid, self.last_scan_time = uid, now
                    print(f"[Room {self.room_id}] UID detected → {uid}")

                    user_id, sched_id, status, reason = self.access_ctrl.check_access(uid, self.room_id)

                    if not user_id:
                        self.log_access(None, uid, None, 'Entry', 'denied')
                        self.send_command(ser, "DENIED")
                        continue

                    role_data = self.db.query("SELECT Role FROM users WHERE User_id=%s", (user_id,), fetchone=True)
                    if not role_data:
                        self.log_access(user_id, uid, None, 'Entry', 'denied')
                        self.send_command(ser, "DENIED")
                        continue

                    role = role_data["Role"].lower()
                    room_status = self.db.query(
                        "SELECT Status FROM classrooms WHERE Room_id=%s", (self.room_id,), fetchone=True
                    )["Status"]

                    if role == "admin":
                        new_status = 'Occupied' if room_status == 'Unoccupied' else 'Unoccupied'
                        self.db.execute("UPDATE classrooms SET Status=%s WHERE Room_id=%s", (new_status, self.room_id))
                        self.log_access(user_id, uid, None, 'Entry/Exit', 'granted')
                        self.send_command(ser, f"\nROOM_ON:{new_status.upper()}")
                        print(f"[Room {self.room_id}] Admin toggled room → {new_status}")
                        continue

                    if role == "faculty" and status == "granted":
                        if room_status == 'Unoccupied':
                            self.db.execute("UPDATE classrooms SET Status='Occupied' WHERE Room_id=%s", (self.room_id,))
                            self.send_command(ser, "\nROOM_ON")
                        else:
                            self.db.execute("UPDATE classrooms SET Status='Unoccupied' WHERE Room_id=%s", (self.room_id,))
                            self.send_command(ser, "\nROOM_OFF")
                        self.log_access(user_id, uid, sched_id, 'Entry/Exit', 'granted')
                        print(f"[Room {self.room_id}] Faculty access processed")
                        continue

                    if role == "student" and status == "granted":
                        if room_status == 'Unoccupied':
                            self.db.execute("UPDATE classrooms SET Status='Occupied' WHERE Room_id=%s", (self.room_id,))
                            self.send_command(ser, "\nROOM_ON")
                        else:
                            self.db.execute("UPDATE classrooms SET Status='Unoccupied' WHERE Room_id=%s", (self.room_id,))
                            self.send_command(ser, "\nROOM_OFF")
                        self.log_access(user_id, uid, sched_id, 'Entry/Exit', 'granted')
                        print(f"[Room {self.room_id}] Student access processed")
                        continue

                    self.send_command(ser, "DENIED")
                    self.log_access(user_id, uid, sched_id, 'Entry', 'denied')

        except Exception as e:
            print(f"[Room {self.room_id}] Reader error: {e}")

# ================== RFID MANAGER CLASS ==================
class RFIDManager:
    def __init__(self, db: Database):
        self.db = db
        self.access_ctrl = AccessControl(db)
        self.readers = []

    def load_readers(self):
        readers = self.db.query("SELECT Room_id, Port_name FROM rfid_reader WHERE Status='Active'")
        if not readers:
            print("No active RFID readers found.")
            return

        for r in readers:
            reader = RFIDReader(r["Port_name"], r["Room_id"], self.db, self.access_ctrl)
            self.readers.append(reader)
            reader.start()
            print(f"Started reader for Room {r['Room_id']} on {r['Port_name']}")

    def run(self):
        self.load_readers()
        auto_off = AutoOffMonitor(self.db)
        auto_off.start()

        try:
            while True:
                time.sleep(1)
        except KeyboardInterrupt:
            print("Service stopped manually.")

# ================== MAIN ==================
if __name__ == "__main__":
    db = Database()
    manager = RFIDManager(db)
    manager.run()
