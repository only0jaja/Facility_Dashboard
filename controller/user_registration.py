import serial
import serial.tools.list_ports
import threading
import time
import mysql.connector
from flask import Flask, jsonify
from flask_cors import CORS

# -------------------------------
# CONFIGURATION
# -------------------------------
BAUD_RATE = 9600


# Set COM port manually here, or leave None to auto-detect
SERIAL_PORT = "COM3"

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "facility_control_v3"  # Update with your DB name
}

# -------------------------------
# DATABASE CONNECTION
# -------------------------------
db = mysql.connector.connect(**DB_CONFIG)
cursor = db.cursor()

# Table to store latest scanned RFID
cursor.execute("""
CREATE TABLE IF NOT EXISTS latest_rfid (
    id INT PRIMARY KEY,
    uid VARCHAR(50),
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
""")
db.commit()

# -------------------------------
# SERIAL PORT AUTO-DETECT
# -------------------------------
def find_arduino_port():
    ports = list(serial.tools.list_ports.comports())
    for p in ports:
        if "Arduino" in p.description:
            return p.device
    return None

if SERIAL_PORT is None:
    SERIAL_PORT = find_arduino_port()

if SERIAL_PORT is None:
    print("[ERROR] No Arduino detected. Connect Arduino and restart.")
    exit(1)

# -------------------------------
# SERIAL CONNECTION
# -------------------------------
try:
    ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=1)
    time.sleep(2)  # give Arduino time to reset
    print(f"[INFO] Connected to Arduino on {SERIAL_PORT}")
except Exception as e:
    print(f"[ERROR] Could not open serial port {SERIAL_PORT}: {e}")
    exit(1)

# -------------------------------
# FLASK APP
# -------------------------------
app = Flask(__name__)
CORS(app)  
latest_uid = None

# -------------------------------
# READ RFID IN BACKGROUND
# -------------------------------

# Registration mode: only capture UID, do not check access
REGISTRATION_MODE = False 

def read_rfid():
    global latest_uid
    while True:
        try:
            if ser.in_waiting:
                uid = ser.readline().decode().strip()
                if not uid:
                    continue

                latest_uid = uid
                print(f"[RFID] Scanned: {uid}")

                # Save latest UID
                cursor.execute(
                    "REPLACE INTO latest_rfid (id, uid) VALUES (1, %s)",
                    (uid,)
                )
                db.commit()

                # -------------------------
                # REGISTRATION MODE
                # -------------------------
                if REGISTRATION_MODE:
                    print("[MODE] REGISTRATION – UID captured only")
                    continue   # ⬅️ IMPORTANT: exit loop iteration here

                # -------------------------
                # ACCESS MODE
                # -------------------------
                cursor.execute(
                    "SELECT COUNT(*) FROM users WHERE Rfid_tag=%s",
                    (uid,)
                )
                allowed = cursor.fetchone()[0] > 0

                response = "GRANTED\n" if allowed else "DENIED\n"
                ser.write(response.encode())
                print(f"[ACCESS] {response.strip()}")

        except Exception as e:
            print("[ERROR] Reading serial:", e)
            time.sleep(1)
threading.Thread(target=read_rfid, daemon=True).start()

# -------------------------------
# API ENDPOINT
# -------------------------------
@app.route("/api/latest-rfid")
def get_latest_rfid():
    global latest_uid
    uid = latest_uid or ""
    
    # Clear latest_uid after reading
    latest_uid = None
    
    return jsonify({"uid": uid})



# -------------------------------
# RUN FLASK SERVER
# -------------------------------
if __name__ == "__main__":
    print("🚀 RFID Controller running...")
    app.run(
        host="0.0.0.0",
        port=5000,
        debug=False,        # ❗ disable debug
        use_reloader=False # ❗ prevent double serial open
    )