<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MQTT Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MQTT broker connection (Mosquitto/EMQX)
    |
    */

    'host' => env('MQTT_HOST', 'localhost'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel-scooter-app'),
    
    'clean_session' => env('MQTT_CLEAN_SESSION', true),
    'keep_alive' => env('MQTT_KEEP_ALIVE', 60),
    'qos' => env('MQTT_QOS', 1), // Quality of Service: 0, 1, or 2
    'retain' => env('MQTT_RETAIN', true), // Retain messages for offline clients
    
    'timeout' => env('MQTT_TIMEOUT', 10),
    'reconnect_delay' => env('MQTT_RECONNECT_DELAY', 5),
    
    'tls' => [
        'enabled' => env('MQTT_TLS_ENABLED', false),
        'ca_file' => env('MQTT_TLS_CA_FILE'),
        'cert_file' => env('MQTT_TLS_CERT_FILE'),
        'key_file' => env('MQTT_TLS_KEY_FILE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Topic Configuration
    |--------------------------------------------------------------------------
    |
    | Topic patterns for different message types
    |
    */

    'topics' => [
        'commands' => 'scooter/{imei}/commands',
        'status' => 'scooter/{imei}/status',
        'location' => 'scooter/{imei}/location',
    ],

];

