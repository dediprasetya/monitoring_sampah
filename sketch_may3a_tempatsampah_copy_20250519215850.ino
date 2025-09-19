#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <LiquidCrystal_I2C.h>

// WiFi credentials
const char* ssid = "vivo 1938";
const char* password = "dediprasetya";

// HiveMQ Cloud
const char* mqtt_server = "b1d4b1389ad74d338cb784c29bc573d8.s1.eu.hivemq.cloud";
const int mqtt_port = 8883;
const char* mqtt_user = "hivemq.webclient.1746241860208";
const char* mqtt_pass = "3OD012CLYabNqyrc,<.>";

// Ultrasonik
#define trigA 5
#define echoA 18
#define trigB 25
#define echoB 33

WiFiClientSecure secureClient;
PubSubClient client(secureClient);

// LCD
LiquidCrystal_I2C lcd(0x27, 16, 2);

void setup_wifi() {
  Serial.println("Connecting to WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    if (client.connect("ESP32Client", mqtt_user, mqtt_pass)) {
      Serial.println("connected");
      client.publish("sampah/status", "ESP32 Connected (insecure)");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

// Fungsi median dari 3 pembacaan
long medianDistance(int trigPin, int echoPin) {
  long readings[3];
  for (int i = 0; i < 3; i++) {
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    readings[i] = pulseIn(echoPin, HIGH, 30000) * 0.034 / 2;
    delay(50); // jeda antar pembacaan
  }
  // Urutkan
  for (int i = 0; i < 2; i++) {
    for (int j = i + 1; j < 3; j++) {
      if (readings[i] > readings[j]) {
        long temp = readings[i];
        readings[i] = readings[j];
        readings[j] = temp;
      }
    }
  }
  return readings[1]; // ambil nilai tengah (median)
}

void setup() {
  Serial.begin(115200);
  pinMode(trigA, OUTPUT);
  pinMode(echoA, INPUT);
  pinMode(trigB, OUTPUT);
  pinMode(echoB, INPUT);

  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Connecting...");

  setup_wifi();
  secureClient.setInsecure();
  client.setServer(mqtt_server, mqtt_port);

  reconnect();

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MQTT OK (insec)");
}

void loop() {
  if (!client.connected()) reconnect();
  client.loop();

  // Baca sensor A (duluan)
  long distanceA = medianDistance(trigA, echoA);
  delay(300); // beri jeda sebelum baca sensor B

  // Baca sensor B
  long distanceB = medianDistance(trigB, echoB);

  // Publish ke MQTT
  client.publish("sampah/jarakA", String(distanceA).c_str());
  client.publish("sampah/jarakB", String(distanceB).c_str());

  // Tampilkan ke LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("A:" + String(distanceA) + "cm");
  lcd.setCursor(0, 1);
  lcd.print("B:" + String(distanceB) + "cm");

  // Tampilkan info memori ke Serial
  Serial.print("Free Heap Memory: ");
  Serial.print(ESP.getFreeHeap());
  Serial.println(" bytes");


  delay(5000);
}
