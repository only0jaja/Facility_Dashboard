import serial
import threading
import mysql.connector
from datetime import datetime
import time

# ================== DATABASE CLASS ==================
class Database:
    def __init__(self, host="localhost", user="root", password="", database="facility_control_v2"):
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

        # Faculty check — must be their room
        if role == "faculty":
            schedule = self.db.query("""
                SELECT Schedule_id, Room_id
                FROM schedule
                WHERE Faculty_id = %s
                AND Room_id = %s
                AND Day = %s
                AND %s BETWEEN Start_time AND End_time
            """, (user_id, room_id, current_day, current_time), fetchone=True)
        else:  # Student check — must be in same room
            schedule = self.db.query("""
                SELECT s.Schedule_id, s.Room_id
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
    def __init__(self, port, room_id, db: Database, access_ctrl: AccessControl):
        super().__init__(daemon=True)
        self.port = port
        self.room_id = room_id
        self.db = db
        self.access_ctrl = access_ctrl

    def log_access(self, user_id, uid, schedule_id, access_type, status):
        self.db.execute("""
            INSERT INTO access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (user_id, uid, self.room_id, schedule_id, datetime.now(), access_type, status))

    def run(self):
        try:
            ser = serial.Serial(self.port, 9600, timeout=1)
            time.sleep(2)
            print(f"✅ Reader ready on {self.port} for Room {self.room_id}")

            while True:
                if ser.in_waiting > 0:
                    uid = ser.readline().decode('utf-8').strip()
                    if not uid:
                        continue

                    print(f"[Room {self.room_id}] UID scanned: {uid}")
                    user_id, schedule_id, access_status = self.access_ctrl.check_access(uid, self.room_id)

                    # Get role
                    user = self.db.query("SELECT Role FROM users WHERE Rfid_tag = %s", (uid,), fetchone=True)
                    role = user["Role"].lower() if user else ""

                    # Get current room status
                    room = self.db.query("SELECT Status FROM classrooms WHERE Room_id = %s", (self.room_id,), fetchone=True)
                    room_status = room["Status"] if room else "Unoccupied"

                    # Admin exit logic
                    if role == "admin" and room_status == "Occupied":
                        self.log_access(user_id, uid, schedule_id, 'Exit', 'granted')
                        self.db.execute("UPDATE classrooms SET Status = 'Unoccupied' WHERE Room_id = %s", (self.room_id,))
                        ser.write(b'ROOM_OFF\n')
                        print(f"[Room {self.room_id}] Admin exited → Room set to Unoccupied")
                        continue

                    # Normal entry log
                    self.log_access(user_id, uid, schedule_id, 'Entry', access_status)

                    if access_status == "granted":
                        self.db.execute("UPDATE classrooms SET Status = 'Occupied' WHERE Room_id = %s", (self.room_id,))
                        ser.write(b'GRANTED\n')
                        print(f"[Room {self.room_id}] Access Granted ✅")
                    else:
                        ser.write(b'DENIED\n')
                        print(f"[Room {self.room_id}] Access Denied ❌")

        except serial.SerialException as e:
            print(f"❌ Serial error on {self.port}: {e}")

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
