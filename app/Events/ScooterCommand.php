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
     * Get the data to broadcast.
     * Note: Laravel Reverb uses Pusher protocol which requires data to be JSON string.
     * ESP32 must decode the JSON string from the 'data' field.
     */
    public function broadcastWith(): array
    {
        $reverbConfig = config('reverb.apps.apps.0', []);
        
        return [
            'commands' => $this->command,
            'timestamp' => now()->toIso8601String(),
            'timeout' => $reverbConfig['activity_timeout'] ?? env('REVERB_APP_ACTIVITY_TIMEOUT', 120),
            'ping_interval' => $reverbConfig['ping_interval'] ?? env('REVERB_APP_PING_INTERVAL', 60),
        ];
    }
}

