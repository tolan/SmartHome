#include <Arduino.h>
#include <ArduinoJson.h>
#include <WiFi.h>
#include <WiFiClient.h>
#include <WebServer.h>
#include <Update.h>

#define LEDC_BASE_FREQ 5000

const char *VERSION = "1.0.6";

const char *ssid = "YourSSID";
const char *password = "Password";

const int KEEP_ALIVE = 60; // in seconds
int counter = KEEP_ALIVE * 1000;

IPAddress serverIp(192, 168, 1, 1);
WebServer server(80);

WiFiClient client;

void apiHandler(DynamicJsonDocument doc)
{
  int size = doc.size();

  for (int i = 0; i < size; i++)
  {
    const String action = doc[i]["action"];
    Serial.print("Action: ");
    Serial.println(action);

    if (action == "switch" || action == "pwm")
    {
      const int channel = doc[i]["data"]["channel"];
      const int value = doc[i]["data"]["value"];

      ledcWrite(channel, value);
    }
    else if (action == "init")
    {
      const int pin = doc[i]["data"]["pin"];
      const int resolution = doc[i]["data"]["resolution"];
      const int channel = doc[i]["data"]["channel"];
      const int value = doc[i]["data"]["value"];

      ledcSetup(channel, LEDC_BASE_FREQ, resolution);
      ledcAttachPin(pin, channel);
      ledcWrite(channel, value);
    }

    Serial.println("OK");
  }
}

void sendRegistration()
{
  if (client.connect(serverIp, 80))
  {
    const String message = "{\"mac\":\"" + WiFi.macAddress() + "\"}";
    unsigned int messageLength = message.length();

    // Make a HTTP request:
    client.println("POST /api/0/device/register HTTP/1.1");
    client.println("Host: " + serverIp.toString());
    client.println("User-Agent: Arduino/1.0");
    client.println("Connection: close");
    client.println("Content-Type: application/json");
    client.print("Content-Length: ");
    client.println(messageLength);
    client.println();
    client.println(message);
  }
}

void setup()
{
  Serial.begin(115200);
  // Connect to WiFi network
  WiFi.begin(ssid, password);
  Serial.println("");

  // Wait for connection
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(500);
    Serial.print(".");
    counter = counter - 500;
    if (counter == 0)
    {
      ESP.restart();
    }
  }

  Serial.println();

  server.on("/version", HTTP_GET, []() {
    Serial.println("on /version");
    server.sendHeader("Connection", "close");
    server.send(200, "text/plain", VERSION);
  });

  server.on("/keep-alive", HTTP_GET, []() {
    Serial.println("on /keep-alive");
    counter = KEEP_ALIVE * 1000;
    server.sendHeader("Connection", "close");
    server.send(200, "text/plain", "OK");
  });

  server.on("/restart", HTTP_GET, []() {
    Serial.println("on /restart");
    server.sendHeader("Connection", "close");
    server.send(200, "text/plain", "OK");
    ESP.restart();
  });

  server.on("/counter", HTTP_GET, []() {
    Serial.println("on /counter");
    server.sendHeader("Connection", "close");
    server.send(200, "text/plain", String(counter));
  });

  server.on("/api", HTTP_POST, []() {
    Serial.println("on /api");

    String json = server.arg(0);

    DynamicJsonDocument doc(json.length() * 8);
    DeserializationError error = deserializeJson(doc, json);

    Serial.println("Deserialization status:");
    Serial.println(error.c_str());

    server.sendHeader("Connection", "close");
    if (error)
    {
      server.send(500, "text/html", error.c_str());
      return;
    }
    else
    {
      apiHandler(doc);
    }
    server.send(200, "text/html", "OK");
  });

  server.on("/update", HTTP_POST, []() {
    Serial.println("on /update");
    server.sendHeader("Connection", "close");
    server.send(200, "text/plain", (Update.hasError()) ? "FAIL" : "OK");
    ESP.restart();
  }, []() {
    HTTPUpload& upload = server.upload();
    if (upload.status == UPLOAD_FILE_START) {
      Serial.printf("Update: %s\n", upload.filename.c_str());
      Serial.printf("Filesize: %d\n", upload.totalSize);
      Serial.printf("Type: %s\n", upload.type.c_str());

      if (!Update.begin(UPDATE_SIZE_UNKNOWN)) { //start with max available size
        Serial.print("Size:");
        Update.printError(Serial);
      }
    } else if (upload.status == UPLOAD_FILE_WRITE) {
      /* flashing firmware to ESP*/
      if (Update.write(upload.buf, upload.currentSize) != upload.currentSize) {
        Serial.print("Flash:");
        Serial.println(upload.currentSize);
        Update.printError(Serial);
      }
    } else if (upload.status == UPLOAD_FILE_END) {
      if (Update.end(true)) { //true to set the size to the current progress
        Serial.printf("Update Success: %u\nRebooting...\n", upload.totalSize);
      } else {
        Update.printError(Serial);
      }
    }
  });

  Serial.print("Used version:");
  Serial.println(VERSION);

  server.begin();
  counter = KEEP_ALIVE * 1000;
  sendRegistration();
}

void loop()
{
  server.handleClient();
  delay(1);
  if (counter-- == 0)
  {
    ESP.restart();
  }
}