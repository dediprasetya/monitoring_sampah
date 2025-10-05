#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <LiquidCrystal_I2C.h>

// WiFi credentials
const char* ssid = "vivo 1938";
const char* password = "dediprasetya";

// MQTT broker
const char* mqtt_server = "hilirisasi.revolusi-it.com";
const int mqtt_port = 1883;
const char* mqtt_user = "hilirisasi";
const char* mqtt_pass = "penelitianhilirisasi25";

// Ultrasonic pins
#define trigA 5
#define echoA 18
#define trigB 25
#define echoB 33

WiFiClient espClient;
PubSubClient client(espClient);
LiquidCrystal_I2C lcd(0x27, 16, 2);
const int bin_id = 1;

void setup_wifi() {
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Connecting to MQTT...");
    String clientId = "ESP32Client-" + String(random(0xffff), HEX);
    if (client.connect(clientId.c_str(), mqtt_user, mqtt_pass)) {
      Serial.println("connected");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" retrying in 5 seconds");
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
  lcd.print("Connecting...");

  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  reconnect();

  lcd.clear();
  lcd.print("Bin Ready");
}

void loop() {
  if (!client.connected()) reconnect();
  client.loop();

  long jarakA = medianDistance(trigA, echoA);
  long jarakB = medianDistance(trigB, echoB);

  StaticJsonDocument<200> doc;
  doc["id"] = bin_id;
  doc["d1"] = jarakA;
  doc["d2"] = jarakB;

  char buffer[200];
  size_t n = serializeJson(doc, buffer);
  client.publish("tong/sensor", buffer, n);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("A:" + String(jarakA) + " B:" + String(jarakB));
  Serial.print("Sent: ");
  Serial.println(buffer);

  delay(5000);
}
