#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 10      // RFID SDA
#define RST_PIN 9      // RFID RST

#define GREEN_LED 2    // 🟢 Green LED (granted)
#define BUZZER 3       // 🔔 Buzzer pin
#define RELAY_LIGHT 4  // 💡 Light relay
#define RELAY_FAN 5    // 🌬️ Fan relay     
#define RELAY_DOOR 6   // 🚪 Door relay
#define RED_LED 7      // 🔴 Red LED (denied)

#define ROOM_ID 1      // ⚙️ Change this per Arduino (e.g., 1=ROOM101, 2=ROOM102)

MFRC522 rfid(SS_PIN, RST_PIN);

String lastUID = "";
unsigned long lastReadTime = 0;
const unsigned long readDelay = 2000; // prevent double read within 2s

void setup() {
  Serial.begin(9600);
  SPI.begin();
  rfid.PCD_Init();

  pinMode(GREEN_LED, OUTPUT);
  pinMode(RED_LED, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  pinMode(RELAY_LIGHT, OUTPUT);
  pinMode(RELAY_FAN, OUTPUT);
  pinMode(RELAY_DOOR, OUTPUT);

  // All OFF initially
  digitalWrite(GREEN_LED, LOW);
  digitalWrite(RED_LED, LOW);
  digitalWrite(BUZZER, LOW);
  digitalWrite(RELAY_LIGHT, HIGH);
  digitalWrite(RELAY_FAN, HIGH);


}

void loop() {
  // Wait for card
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial())
    return;

  // Read UID
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i] < 0x10 ? " 0" : " ");
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.trim();
  uid.toUpperCase();

  unsigned long currentTime = millis();
  if (uid == lastUID && (currentTime - lastReadTime < readDelay)) return;

  lastUID = uid;
  lastReadTime = currentTime;

  // Send only ROOM_ID and UID to Python
  Serial.println(uid);

  // Wait for Python reply
  unsigned long startTime = millis();
  while (!Serial.available()) {
    if (millis() - startTime > 3000) return; // timeout after 3s
  }

  String response = Serial.readStringUntil('\n');
  response.trim();

  if (response == "GRANTED") {
    grantAccess();
  } else if (response == "DENIED") {
    denyAccess();
  }

  delay(1000);
}

void beep(int times, int duration = 100) {
  for (int i = 0; i < times; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(duration);
    digitalWrite(BUZZER, LOW);
    delay(duration);
  }
}

void grantAccess() {
  // 🟢 Green LED + 2 short beeps
  digitalWrite(GREEN_LED, HIGH);
  beep(2, 100);

  // Unlock door + turn on lights and fan
  digitalWrite(RELAY_DOOR, LOW);
  digitalWrite(RELAY_LIGHT, LOW);
  digitalWrite(RELAY_FAN, LOW);

  delay(5000); // door open for 5 sec

  // Reset everything
  digitalWrite(RELAY_DOOR, HIGH);
  digitalWrite(GREEN_LED, LOW);
}

void denyAccess() {
  // 🔴 Red LED + 3 beeps
  digitalWrite(RED_LED, HIGH);
  beep(3, 150);

  // Flash door relay quickly to indicate denial
  for (int i = 0; i < 2; i++) {
    digitalWrite(RELAY_DOOR, LOW);
    delay(150);
    digitalWrite(RELAY_DOOR, HIGH);
    delay(150);
  }

  digitalWrite(RED_LED, LOW);
}
