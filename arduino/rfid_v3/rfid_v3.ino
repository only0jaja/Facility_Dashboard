#include <SPI.h>
#include <MFRC522.h>

// -------------------------------
// PIN DEFINITIONS
// -------------------------------
#define SS_PIN 10      // RFID SDA
#define RST_PIN 9      // RFID RST

#define GREEN_LED 2    // 🟢 Green LED (granted)
#define BUZZER 3       // 🔔 Buzzer
#define RELAY_LIGHT 4  // 💡 Light relay
#define RELAY_FAN 5    // 🌬️ Fan relay     
#define RELAY_DOOR 6   // 🚪 Door relay
#define RED_LED 7      // 🔴 Red LED (denied)

#define ROOM_ID 1      // ⚙️ Room ID (for Python)

MFRC522 rfid(SS_PIN, RST_PIN);

// -------------------------------
// VARIABLES
// -------------------------------
String lastUID = "";
unsigned long lastReadTime = 0;
const unsigned long readDelay = 2000; // 2s delay to prevent double read

// -------------------------------
// SETUP
// -------------------------------
void setup() {
  Serial.begin(9600);
  SPI.begin();
  rfid.PCD_Init();

  // Set pins
  pinMode(GREEN_LED, OUTPUT);
  pinMode(RED_LED, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  pinMode(RELAY_LIGHT, OUTPUT);
  pinMode(RELAY_FAN, OUTPUT);
  pinMode(RELAY_DOOR, OUTPUT);

  // Turn off outputs initially
  digitalWrite(GREEN_LED, LOW);
  digitalWrite(RED_LED, LOW);
  digitalWrite(BUZZER, LOW);
  digitalWrite(RELAY_LIGHT, HIGH);
  digitalWrite(RELAY_FAN, HIGH);

  Serial.println("🚀 RFID Reader Ready");
}

// -------------------------------
// LOOP
// -------------------------------
void loop() {
  // Wait for a new card
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) return;

  // Build UID string (format: XX:XX:XX:XX)
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (i > 0) uid += ":";
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  unsigned long currentTime = millis();
  // Prevent duplicate reads
  if (uid == lastUID && (currentTime - lastReadTime < readDelay)) return;

  lastUID = uid;
  lastReadTime = currentTime;

  // Send UID to Python
  Serial.println(uid);

  // Wait for Python response (timeout 3s)
  unsigned long startTime = millis();
  while (!Serial.available()) {
    if (millis() - startTime > 3000) return;
  }

  String response = Serial.readStringUntil('\n');
  response.trim();

  if (response == "GRANTED") {
    grantAccess();
  } else if (response == "DENIED") {
    denyAccess();
  }

  delay(100); // small delay to avoid flooding
}

// -------------------------------
// BEEP FUNCTION
// -------------------------------
void beep(int times, int duration = 100) {
  for (int i = 0; i < times; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(duration);
    digitalWrite(BUZZER, LOW);
    delay(duration);
  }
}

// -------------------------------
// GRANT ACCESS FUNCTION
// -------------------------------
void grantAccess() {
  digitalWrite(GREEN_LED, HIGH);
  beep(2, 100);

  // Unlock door + lights + fan
  digitalWrite(RELAY_DOOR, LOW);
  digitalWrite(RELAY_LIGHT, LOW);
  digitalWrite(RELAY_FAN, LOW);

  delay(5000); // door open 5s

  // Reset
  digitalWrite(RELAY_DOOR, HIGH);
  digitalWrite(RELAY_LIGHT, HIGH);
  digitalWrite(RELAY_FAN, HIGH);
  digitalWrite(GREEN_LED, LOW);
}

// -------------------------------
// DENY ACCESS FUNCTION
// -------------------------------
void denyAccess() {
  digitalWrite(RED_LED, HIGH);
  beep(3, 150);

  // Flash door relay as warning
  for (int i = 0; i < 2; i++) {
    digitalWrite(RELAY_DOOR, LOW);
    delay(150);
    digitalWrite(RELAY_DOOR, HIGH);
    delay(150);
  }

  digitalWrite(RED_LED, LOW);
}
