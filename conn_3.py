import serial
import threading
import mysql.connector
from datetime import datetime
import time

# ================== DATABASE CONNECTION ==================
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="facility_control_v2"
)
cursor = db.cursor(dictionary=True)

# ================== ACCESS CHECK FUNCTION ==================
def check_access(uid):
    now = datetime.now()
    current_day = now.strftime("%a")  # Mon, Tue, ...
    current_time = now.strftime("%H:%M:%S")

    # 1. Find user
    cursor.execute("""
        SELECT User_id, Status, CourseSection_id, Role
        FROM users
        WHERE Rfid_tag = %s
    """, (uid,))
    user = cursor.fetchone()

    if not user:
        return None, None, "denied"

    if user["Status"] != "Active":
        return user["User_id"], None, "denied"

    user_id = user["User_id"]

    # Admins can access anytime
    if user["Role"].lower() == "admin":
        return user_id, None, "granted"

    # Faculty: check schedule
    if user["Role"].lower() == "faculty":
        cursor.execute("""
            SELECT Schedule_id, Room_id
            FROM schedule
            WHERE Faculty_id = %s
            AND Day = %s
            AND %s BETWEEN Start_time AND End_time
        """, (user_id, current_day, current_time))
    else:  # Student: check schedule via schedule_access
        cursor.execute("""
            SELECT s.Schedule_id, s.Room_id
            FROM schedule s
            JOIN schedule_access sa ON sa.Schedule_id = s.Schedule_id
            WHERE sa.CourseSection_id = %s
            AND s.Day = %s
            AND %s BETWEEN s.Start_time AND s.End_time
        """, (user["CourseSection_id"], current_day, current_time))

    schedule = cursor.fetchone()
    if schedule:
        return user_id, schedule["Schedule_id"], "granted"
    else:
        return user_id, None, "denied"

# ================== HANDLE READER ==================
def handle_reader(port, room_id):
    try:
        ser = serial.Serial(port, 9600, timeout=1)
        time.sleep(2)
        print(f"✅ Reader ready on {port} for Room {room_id}")

        while True:
            if ser.in_waiting > 0:
                uid = ser.readline().decode('utf-8').strip()
                if not uid:
                    continue

                print(f"[Room {room_id}] UID scanned: {uid}")
                user_id, schedule_id, access_status = check_access(uid)

                # Get role
                cursor.execute("SELECT Role FROM users WHERE Rfid_tag = %s", (uid,))
                role_row = cursor.fetchone()
                role = role_row["Role"].lower() if role_row else ""

                # Get current room status
                cursor.execute("SELECT Status FROM classrooms WHERE Room_id = %s", (room_id,))
                room = cursor.fetchone()
                room_status = room["Status"] if room else "Unoccupied"

                # Admin exit
                if role == "admin" and room_status == "Occupied":
                    cursor.execute("""
                        INSERT INTO access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
                        VALUES (%s, %s, %s, %s, %s, 'Exit', 'granted')
                    """, (user_id, uid, room_id, schedule_id, datetime.now()))
                    db.commit()

                    cursor.execute("UPDATE classrooms SET Status = 'Unoccupied' WHERE Room_id = %s", (room_id,))
                    db.commit()
                    ser.write(b'ROOM_OFF\n')
                    print(f"[Room {room_id}] Admin exited → Room set to Unoccupied")
                    continue

                # Normal access
                cursor.execute("""
                    INSERT INTO access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
                    VALUES (%s, %s, %s, %s, %s, 'Entry', %s)
                """, (user_id, uid, room_id, schedule_id, datetime.now(), access_status))
                db.commit()

                if access_status == "granted":
                    cursor.execute("UPDATE classrooms SET Status = 'Occupied' WHERE Room_id = %s", (room_id,))
                    db.commit()
                    ser.write(b'GRANTED\n')
                    print(f"[Room {room_id}] Access Granted ✅")
                else:
                    ser.write(b'DENIED\n')
                    print(f"[Room {room_id}] Access Denied ❌")

    except serial.SerialException as e:
        print(f"❌ Serial error on {port}: {e}")

# ================== START THREADS ==================
threading.Thread(target=handle_reader, args=('COM9', 1), daemon=True).start()
threading.Thread(target=handle_reader, args=('COM10', 2), daemon=True).start()

print("RFID Access System Ready")

while True:
    time.sleep(1)
