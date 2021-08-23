#define _TASK_STD_FUNCTION      // Compile with support for std::function
#define _TASK_SLEEP_ON_IDLE_RUN // Enable 1 ms SLEEP_IDLE powerdowns between tasks if no callback methods were invoked during the pass

#include <Arduino.h>
#include <ArduinoJson.h>
#include <WiFi.h>
#include <WiFiClient.h>
#include <WebServer.h>
#include <Update.h>
#include <TaskScheduler.h>

#define LEDC_BASE_FREQ 5000

const char *VERSION = "1.2.0";

const char *ssid = "Zom-AP-IoT";
const char *password = "100807962";

const int KEEP_ALIVE = 90; // in seconds
int counter = KEEP_ALIVE * 1000;

IPAddress serverIp(10, 109, 97, 190);
WebServer server(80);

WiFiClient client;

Scheduler ts;
Task schedulerTask;

int outputPins[4] = {23, 22, 1, 3};

int *disablePins;
int *enablePins;
int *blockPins;

void setLedValue(int channel, int value, int previous)
{
  if (value != previous)
  {
    int maxSteps = 200;
    float step = (value - previous) / maxSteps;
    if (value > previous)
    {
      for (float i = previous; i <= value; i = i + step)
      {
        ledcWrite(channel, round(i));
        delay(1);
      }
    }
    else
    {
      for (float i = previous; i >= value; i = i + step)
      {
        ledcWrite(channel, round(i));
        delay(1);
      }
    }
  }
  ledcWrite(channel, value);
}

int *getPins(DynamicJsonDocument doc, String name)
{
  int count = doc[name].size();
  int *result = new int[count + 1];

  result[0] = count;
  for (int j = 0; j < count; j++)
  {
    result[j + 1] = doc[name][j];
  }

  return result;
}

void IRAM_ATTR stopUpDown()
{
  Serial.println("STOP!!!");
  Serial.print("Disable pins:");
  ts.disableAll();
  for (int j = 0; j < enablePins[0]; j++)
  {
    Serial.print(" ");
    Serial.print(enablePins[j + 1]);
    digitalWrite(enablePins[j + 1], LOW);
  }

  Serial.println(".");
  for (int j = 0; j < blockPins[0]; j++)
  {
    detachInterrupt(blockPins[j + 1]);
  }
}

void apiHandler(DynamicJsonDocument doc)
{
  int size = doc.size();

  for (int i = 0; i < size; i++)
  {
    String action = doc[i]["action"];
    Serial.print("Action: ");
    Serial.println(action);

    if (action == "switch")
    {
      int channel = doc[i]["data"]["channel"];
      int value = doc[i]["data"]["value"];

      setLedValue(channel, (int)value, 0);
    }
    else if (action == "pwm")
    {
      int channel = doc[i]["data"]["channel"];
      int value = doc[i]["data"]["value"];
      int previous = isDigit(doc[i]["data"]["previous"]) ? doc[i]["data"]["previous"] : value;

      setLedValue(channel, value, previous);
    }
    else if (action == "up_down")
    {
      stopUpDown();
      disablePins = getPins(doc[i]["data"], "disablePins");
      enablePins = getPins(doc[i]["data"], "enablePins");
      blockPins = getPins(doc[i]["data"], "blockPins");
      int blockDuration = doc[i]["data"]["blockDuration"];

      for (int j = 0; j < disablePins[0]; j++)
      {
        pinMode(disablePins[j + 1], OUTPUT);
        digitalWrite(disablePins[j + 1], LOW);
      }

      delay(10);

      for (int j = 0; j < enablePins[0]; j++)
      {
        pinMode(enablePins[j + 1], OUTPUT);
        digitalWrite(enablePins[j + 1], HIGH);
      }

      for (int j = 0; j < blockPins[0]; j++)
      {
        pinMode(blockPins[j + 1], INPUT_PULLUP);
        attachInterrupt(blockPins[j + 1], stopUpDown, FALLING);
      }

      if (blockDuration > 0 && enablePins[0] > 0)
      {
        schedulerTask.enableDelayed(blockDuration * 1000);
      }
    }
    else if (action == "light_init")
    {
      int pin = doc[i]["data"]["pin"];
      int resolution = doc[i]["data"]["resolution"];
      int channel = doc[i]["data"]["channel"];
      int value = doc[i]["data"]["value"];

      ledcSetup(channel, LEDC_BASE_FREQ, resolution);
      ledcAttachPin(pin, channel);
      setLedValue(channel, value, value);
    }
    else if (action == "engine_init")
    {
      disablePins = getPins(doc[i]["data"], "disablePins");
      enablePins = getPins(doc[i]["data"], "enablePins");
      blockPins = getPins(doc[i]["data"], "blockPins");
      int blockDuration = doc[i]["data"]["blockDuration"];

      for (int j = 0; j < disablePins[0]; j++)
      {
        pinMode(disablePins[j + 1], OUTPUT);
        digitalWrite(disablePins[j + 1], LOW);
      }

      delay(10);

      for (int j = 0; j < enablePins[0]; j++)
      {
        pinMode(enablePins[j + 1], OUTPUT);
        digitalWrite(enablePins[j + 1], HIGH);
      }

      for (int j = 0; j < blockPins[0]; j++)
      {
        pinMode(blockPins[j + 1], INPUT_PULLUP);
        attachInterrupt(blockPins[j + 1], stopUpDown, FALLING);
      }

      if (blockDuration > 0 && enablePins[0] > 0)
      {
        schedulerTask.enableDelayed(blockDuration * 1000);
      }
    }
    Serial.println("OK");
  }
}

void sendRegistration()
{
  if (client.connect(serverIp, 8888))
  {
    Serial.println("Connected to registration");
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
    Serial.println("Registration is completed");
  }
  else
  {
    Serial.println("Unreachable registration");
  }
}

void setup()
{
  Serial.begin(115200);
  Serial.println();
  Serial.println("Init output pins... ");

  int countOutputs = (sizeof(outputPins) / sizeof(outputPins[0]));
  for (int i = 0; i < countOutputs; i++)
  {
    Serial.print("Set pin ");
    Serial.print(outputPins[i]);
    Serial.println(" to LOW.");
    pinMode(outputPins[i], OUTPUT);
    digitalWrite(outputPins[i], LOW);
  }

  // Connect to WiFi network
  WiFi.begin(ssid, password);
  Serial.println();
  Serial.println("Connecting to WiFi... ");

  // Wait for connection
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(500);
    Serial.print(".");
    counter = counter - 500;
    if (counter == 0)
    {
      Serial.println();
      Serial.print("ESP restart.");
      ESP.restart();
    }
  }

  Serial.println();
  Serial.print("WiFi connected.");

  server.on("/version", HTTP_GET, []()
            {
              Serial.println("on /version");
              server.sendHeader("Connection", "close");
              server.send(200, "text/plain", VERSION);
            });

  server.on("/keep-alive", HTTP_GET, []()
            {
              Serial.println("on /keep-alive");
              counter = KEEP_ALIVE * 1000;
              server.sendHeader("Connection", "close");
              server.send(200, "text/plain", "OK");
            });

  server.on("/restart", HTTP_GET, []()
            {
              Serial.println("on /restart");
              server.sendHeader("Connection", "close");
              server.send(200, "text/plain", "OK");
              counter = 0;
            });

  server.on("/counter", HTTP_GET, []()
            {
              Serial.println("on /counter");
              server.sendHeader("Connection", "close");
              server.send(200, "text/plain", String(counter));
            });

  server.on("/api", HTTP_POST, []()
            {
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

  server.on(
      "/update", HTTP_POST, []()
      {
        Serial.println("on /update");
        server.sendHeader("Connection", "close");
        server.send(200, "text/plain", (Update.hasError()) ? "FAIL" : "OK");
        ESP.restart();
      },
      []()
      {
        HTTPUpload &upload = server.upload();
        if (upload.status == UPLOAD_FILE_START)
        {
          Serial.printf("Update: %s\n", upload.filename.c_str());
          Serial.printf("Filesize: %d\n", upload.totalSize);
          Serial.printf("Type: %s\n", upload.type.c_str());

          if (!Update.begin(UPDATE_SIZE_UNKNOWN))
          { //start with max available size
            Serial.print("Size:");
            Update.printError(Serial);
          }
        }
        else if (upload.status == UPLOAD_FILE_WRITE)
        {
          /* flashing firmware to ESP*/
          if (Update.write(upload.buf, upload.currentSize) != upload.currentSize)
          {
            Serial.print("Flash:");
            Serial.println(upload.currentSize);
            Update.printError(Serial);
          }
        }
        else if (upload.status == UPLOAD_FILE_END)
        {
          if (Update.end(true))
          { //true to set the size to the current progress
            Serial.printf("Update Success: %u\nRebooting...\n", upload.totalSize);
          }
          else
          {
            Update.printError(Serial);
          }
        }
      });

  Serial.println();
  Serial.print("Used version:");
  Serial.println(VERSION);

  // Initialize scheduler
  ts.init();
  ts.addTask(schedulerTask);
  schedulerTask.setCallback(&stopUpDown);
  schedulerTask.setIterations(TASK_FOREVER);

  server.begin();
  counter = KEEP_ALIVE * 1000;
  Serial.println("Send registration...");
  sendRegistration();
}

void loop()
{
  server.handleClient();
  ts.execute();
  if (counter-- <= 0)
  {
    Serial.println("ESP restart.");
    ESP.restart();
  }

  delay(1);
}