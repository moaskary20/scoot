# MQTT Setup Guide - Mosquitto Installation

## تثبيت Mosquitto Broker

### على Ubuntu/Debian:
```bash
sudo apt update
sudo apt install mosquitto mosquitto-clients -y
```

### على CentOS/RHEL:
```bash
sudo yum install epel-release
sudo yum install mosquitto mosquitto-clients -y
```

### التحقق من التثبيت:
```bash
mosquitto -v
```

### تشغيل Mosquitto:
```bash
sudo systemctl start mosquitto
sudo systemctl enable mosquitto
sudo systemctl status mosquitto
```

### إعداد Configuration (اختياري):
```bash
sudo nano /etc/mosquitto/mosquitto.conf
```

إعدادات أساسية:
```
listener 1883
allow_anonymous true  # للتطوير فقط - في الإنتاج استخدم authentication
```

### إعادة تشغيل Mosquitto:
```bash
sudo systemctl restart mosquitto
```

### اختبار الاتصال:
```bash
# Terminal 1 - Subscribe
mosquitto_sub -h localhost -t test/topic

# Terminal 2 - Publish
mosquitto_pub -h localhost -t test/topic -m "Hello MQTT"
```

### Firewall (إذا لزم الأمر):
```bash
sudo ufw allow 1883/tcp
```

