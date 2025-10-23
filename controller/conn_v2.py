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
    database="facility_management"
)
cursor = db.cursor(dictionary=True)

# ================== ACCESS CHECK FUNCTION ==================
def check_access(uid):
    now = datetime.now()
    current_day = now.strftime("%A")
    current_time = now.strftime("%H:%M:%S")

    # 1. Find user
    cursor.execute("""
        SELECT User_id, Status, Course, Section, Year, Semester, Role
        FROM Users
        WHERE Rfid_tag = %s
    """, (uid,))
    user = cursor.fetchone()

    if not user:
        return None, None, "denied"

    if user["Status"] != "Active":
        return user["User_id"], None, "denied"

    user_id = user["User_id"]

    # ✅ Admins can access anytime
    if user["Role"].lower() == "admin":
        return user_id, None, "granted"

    # 2. Check schedule
    if user["Role"].lower() == "faculty":
        cursor.execute("""
            SELECT Schedule_id, Room_id
            FROM Schedule
            WHERE Faculty_id = %s
            AND Day = %s
            AND %s BETWEEN Start_time AND End_time
        """, (user_id, current_day, current_time))
    else:
        cursor.execute("""
            SELECT s.Schedule_id, s.Room_id
            FROM Schedule s
            JOIN Schedule_access sa ON sa.Schedule_id = s.Schedule_id
            WHERE sa.Course = %s
            AND sa.Section = %s
            AND sa.Year = %s
            AND sa.Semester = %s
            AND s.Day = %s
            AND %s BETWEEN s.Start_time AND s.End_time
        """, (user["Course"], user["Section"], user["Year"],
              user["Semester"], current_day, current_time))

    schedule = cursor.fetchone()

    if schedule:
        return user_id, schedule["Schedule_id"], "granted"
    else:
        return user_id, None, "denied"


# ================== HANDLE EACH ARDUINO ==================
def handle_reader(port, room_id):
    try:
        ser = serial.Serial(port, 9600, timeout=1)
        time.sleep(2)
        print(f"✅ Reader for Room {room_id} ready on {port}")

        while True:
            if ser.in_waiting > 0:
                data = ser.readline().decode('utf-8').strip()
                if not data:
                    continue

                # Arduino sends "room_id,uid"
                if ',' in data:
                    _, uid = data.split(',', 1)
                else:
                    uid = data.strip()

                print(f"[Room {room_id}] Scanned UID: {uid}")

                user_id, schedule_id, access_status = check_access(uid)

                # Get user role
                cursor.execute("SELECT Role FROM Users WHERE Rfid_tag = %s", (uid,))
                role_row = cursor.fetchone()
                role = role_row["Role"].lower() if role_row else ""

                # Get current room status
                cursor.execute("SELECT Status FROM Classrooms WHERE Room_id = %s", (room_id,))
                room = cursor.fetchone()
                room_status = room["Status"] if room else "Unoccupied"

                # ================== ADMIN EXIT LOGIC ==================
                if role == "admin" and room_status == "Occupied":
                    # Log exit
                    cursor.execute("""
                        INSERT INTO Access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
                        VALUES (%s, %s, %s, %s, %s, %s, %s)
                    """, (
                        user_id, uid, room_id, schedule_id,
                        datetime.now(), "Exit", "granted"
                    ))
                    db.commit()

                    # Update room status to Unoccupied
                    cursor.execute("""
                        UPDATE Classrooms
                        SET Status = 'Unoccupied'
                        WHERE Room_id = %s
                    """, (room_id,))
                    db.commit()

                    # Tell Arduino to turn OFF
                    ser.write(b'DENIED\n')  # reuse denied signal for OFF
                    print(f"[Room {room_id}] Admin tapped again → Room turned OFF and set to Unoccupied 🔴")
                    continue

                # ================== NORMAL ACCESS FLOW ==================
                # Log access attempt
                cursor.execute("""
                    INSERT INTO Access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
                    VALUES (%s, %s, %s, %s, %s, %s, %s)
                """, (
                    user_id, uid, room_id, schedule_id,
                    datetime.now(), "Entry", access_status
                ))
                db.commit()

                if access_status == "granted":
                    cursor.execute("""
                        UPDATE Classrooms
                        SET Status = 'Occupied'
                        WHERE Room_id = %s
                    """, (room_id,))
                    db.commit()

                    ser.write(b'GRANTED\n')
                    print(f"[Room {room_id}] Access Granted ✅ (Room set to Occupied)")
                else:
                    ser.write(b'DENIED\n')
                    print(f"[Room {room_id}] Access Denied ❌")

    except serial.SerialException as e:
        print(f"❌ Error opening {port}: {e}")


# ================== START THREADS FOR EACH ROOM ==================
threading.Thread(target=handle_reader, args=('COM9', 1), daemon=True).start()   # ROOM101
threading.Thread(target=handle_reader, args=('COM10', 2), daemon=True).start()  # ROOM102

print("RFID Access System Ready for all readers...")

# Keep script alive
while True:
    time.sleep(1)
