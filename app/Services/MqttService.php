<?php

namespace App\Services;

use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\Log;

class MqttService
{
    private ?phpMQTT $client = null;

    /**
     * Get or create MQTT client connection
     */
    private function getClient(): phpMQTT
    {
        if ($this->client === null) {
            $host = config('mqtt.host', 'localhost');
            $port = config('mqtt.port', 1883);
            $clientId = config('mqtt.client_id', 'laravel-scooter-app-' . uniqid());
            $username = config('mqtt.username');
            $password = config('mqtt.password');

            $this->client = new phpMQTT($host, $port, $clientId);

            if (!$this->client->connect(true, null, $username, $password)) {
                Log::error('❌ MQTT: Failed to connect to broker', [
                    'host' => $host,
                    'port' => $port,
                ]);
                throw new \Exception('Failed to connect to MQTT broker');
            }

            Log::info('✅ MQTT: Connected to broker', [
                'host' => $host,
                'port' => $port,
                'client_id' => $clientId,
            ]);
        }

        return $this->client;
    }

    /**
     * Publish command to scooter via MQTT
     */
    public function publishCommand(string $imei, array $command): void
    {
        Log::info('📡 MQTT: Publishing command', [
            'imei' => $imei,
            'command' => $command,
        ]);

        if (!$imei) {
            Log::warning('❌ MQTT: Cannot publish command: IMEI is empty');
            return;
        }

        try {
            $client = $this->getClient();
            
            $reverbConfig = config('reverb.apps.apps.0', []);
            
            $payload = [
                'event' => 'command',
                'data' => [
                    'commands' => $command,
                    'timestamp' => now()->toIso8601String(),
                    'timeout' => $reverbConfig['activity_timeout'] ?? env('REVERB_APP_ACTIVITY_TIMEOUT', 120),
                    'ping_interval' => $reverbConfig['ping_interval'] ?? env('REVERB_APP_PING_INTERVAL', 60),
                ],
            ];

            $topic = str_replace('{imei}', $imei, config('mqtt.topics.commands', 'scooter/{imei}/commands'));
            $qos = config('mqtt.qos', 1);
            $retain = config('mqtt.retain', true) ? 1 : 0;

            Log::info('📡 MQTT: Publishing to topic', [
                'topic' => $topic,
                'qos' => $qos,
                'retain' => $retain,
                'payload' => $payload,
            ]);

            $client->publish($topic, json_encode($payload, JSON_UNESCAPED_UNICODE), $qos, $retain);

            Log::info('✅ MQTT: Command published successfully', [
                'topic' => $topic,
                'imei' => $imei,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ MQTT: Failed to publish command', [
                'imei' => $imei,
                'command' => $command,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Disconnect MQTT client
     */
    public function disconnect(): void
    {
        if ($this->client !== null) {
            try {
                $this->client->close();
                $this->client = null;
                Log::info('✅ MQTT: Disconnected');
            } catch (\Exception $e) {
                Log::error('❌ MQTT: Error disconnecting', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Reconnect MQTT client
     */
    public function reconnect(): void
    {
        $this->disconnect();
        $this->client = null;
        $this->getClient();
    }
}

