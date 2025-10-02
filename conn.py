import serial
import mysql.connector
from datetime import datetime

# Database connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="facility_management"
)
cursor = db.cursor()

# Arduino Serial Port
ser = serial.Serial('COM9', 9600, timeout=1)

while True:
    if ser.in_waiting > 0:
        uid = ser.readline().decode('utf-8').strip()
        
        # Ignore invalid or empty values
        if not uid:
            continue
        
        print(f"{uid}")
        
        # Lookup user
        cursor.execute("SELECT User_id, F_name, L_name, Status FROM Users WHERE Rfid_tag = %s", (uid,))
        user = cursor.fetchone()
        
        if user:
            user_id, fname, lname, status = user
            access_status = "granted" if status == "Active" else "denied"
        else:
            access_status = "denied"
            user_id = None
        
        # Insert into access_log
        cursor.execute("""
            INSERT INTO Access_log (User_id, Rfid_tag, Room_id, Schedule_id, Access_time, Access_type, Status)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (
            user_id, uid, 1, None, datetime.now(), "Entry", access_status
        ))
        db.commit()
