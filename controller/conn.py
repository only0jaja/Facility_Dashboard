import serial
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
cursor = db.cursor(dictionary=True)  # easier to handle with column names

# ================== SERIAL CONNECTION ==================
ser = serial.Serial('COM9', 9600, timeout=1)
time.sleep(2)  # wait for Arduino to reset

print("RFID Access System Ready...")

# ================== FUNCTION ==================
def check_access(uid):
    now = datetime.now()
    current_day = now.strftime("%A")   # 'Monday', 'Tuesday'
    current_time = now.strftime("%H:%M:%S")

    # 1. Find user
    cursor.execute("SELECT User_id, Status, Course, Section, Year, Semester, Role FROM Users WHERE Rfid_tag = %s", (uid,))
    user = cursor.fetchone()

    if not user:
        return None, "denied"

    if user["Status"] != "Active":
        return user["User_id"], "denied"

    user_id = user["User_id"]

    # ✅ Admins can access anytime, anywhere
    if user["Role"].lower() == "admin":
        return user_id, "granted"

    # 2. Check schedule
    if user["Role"].lower() == "faculty":
        # Faculty: check if they are the assigned instructor
        cursor.execute("""
            SELECT Schedule_id, Room_id FROM Schedule
            WHERE Faculty_id = %s
            AND Day = %s
            AND %s BETWEEN Start_time AND End_time
        """, (user_id, current_day, current_time))
    else:
        # Student: check if schedule allows their course/section/year
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
        """, (user["Course"], user["Section"], user["Year"], user["Semester"], current_day, current_time))

    schedule = cursor.fetchone()

    if schedule:
        return user_id, "granted"
    else:
        return user_id, "denied"


# ================== MAIN LOOP ==================
while True:
    if ser.in_waiting > 0:
        uid = ser.readline().decode('utf-8').strip()

        if not uid:
            continue

        print(f"Scanned UID: {uid}")
        user_id, access_status = check_access(uid)

        # 3. Insert into logs
        cursor.execute("""
            INSERT INTO Access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (
            user_id, uid,
            1, None,  # Example: default Room_id=1, Schedule_id NULL if not found
            datetime.now(), "Entry", access_status
        ))
        db.commit()

        # 4. Send command to Arduino
        if access_status == "granted":
            ser.write(b'GRANTED\n')   # Arduino unlocks door
            print("Access Granted ✅")
        else:
            ser.write(b'DENIED\n')    # Arduino keeps locked
            print("Access Denied ❌")
