<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\BroadcastMessage;

class ScooterCommand implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The name of the queue the event should be sent to.
     */
    public $broadcastQueue = 'default';

    public function __construct(
        public string $imei,
        public array $command
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('scooter.' . $this->imei);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'command';
    }

    /**
     * Customize the broadcast message to send data as JSON object instead of JSON string.
     * This method overrides the default behavior to send data as object directly.
     */
    public function toBroadcast($notifiable = null): BroadcastMessage
    {
        $reverbConfig = config('reverb.apps.apps.0', []);
        
        $data = [
            'commands' => $this->command,
            'timestamp' => now()->toIso8601String(),
            'timeout' => $reverbConfig['activity_timeout'] ?? env('REVERB_APP_ACTIVITY_TIMEOUT', 120),
            'ping_interval' => $reverbConfig['ping_interval'] ?? env('REVERB_APP_PING_INTERVAL', 60),
        ];

        return new BroadcastMessage([
            'event' => $this->broadcastAs(),
            'data' => $data, // Send as object, not string - ESP32 expects JSON object directly
            'channel' => 'scooter.' . $this->imei,
        ]);
    }
}

