#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <LiquidCrystal_I2C.h>

// WiFi credentials
const char* ssid = "vivo 1938";
const char* password = "dediprasetya";

// MQTT server
const char* mqtt_server = "hilirisasi.revolusi-it.com";
const int mqtt_port = 1883;
const char* mqtt_user = "hilirisasi";
const char* mqtt_pass = "penelitianhilirisasi25";

// Ultrasonic pins
#define trigA 5
#define echoA 18
#define trigB 25
#define echoB 33

WiFiClientSecure secureClient;
PubSubClient client(secureClient);

// LCD
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ID tong (ubah sesuai kebutuhan)
const int idTong = 1;  

void setup_wifi() {
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println(" connected!");
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Menghubungkan ke MQTT...");
    if (client.connect("ESP32_bin1", mqtt_user, mqtt_pass)) {
      Serial.println("terhubung");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" mencoba dalam 5 detik");
      delay(5000);
    }
  }
}

long medianDistance(int trigPin, int echoPin) {
  long readings[3];
  for (int i = 0; i < 3; i++) {
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    readings[i] = pulseIn(echoPin, HIGH, 30000) * 0.034 / 2;
    delay(50);
  }
  // ambil median dari 3 data
  for (int i = 0; i < 2; i++) {
    for (int j = i + 1; j < 3; j++) {
      if (readings[i] > readings[j]) {
        long temp = readings[i];
        readings[i] = readings[j];
        readings[j] = temp;
      }
    }
  }
  return readings[1];
}

void setup() {
  Serial.begin(115200);
  pinMode(trigA, OUTPUT);
  pinMode(echoA, INPUT);
  pinMode(trigB, OUTPUT);
  pinMode(echoB, INPUT);

  lcd.init();
  lcd.backlight();
  lcd.print("Menghubungkan...");

  setup_wifi();
  secureClient.setInsecure();
  client.setServer(mqtt_server, mqtt_port);
  reconnect();

  lcd.clear();
  lcd.print("Tong Ready");
}

void loop() {
  if (!client.connected()) reconnect();
  client.loop();

  long distance1 = medianDistance(trigA, echoA);
  long distance2 = medianDistance(trigB, echoB);

  // Buat JSON { "id":1, "d1":..., "d2":... }
  StaticJsonDocument<200> doc;
  doc["id"] = idTong;
  doc["d1"] = distance1;
  doc["d2"] = distance2;

  char buffer[200];
  size_t n = serializeJson(doc, buffer);
  client.publish("tong/sensor", buffer, n);

  // Tampilkan di LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Sensor A:" + String(distance1) + "cm");
  lcd.setCursor(0, 1);
  lcd.print("Sensor B:" + String(distance2) + "cm");

  // Tampilkan juga di Serial Monitor
  Serial.print("JSON sent: ");
  Serial.println(buffer);

  delay(5000);
}
