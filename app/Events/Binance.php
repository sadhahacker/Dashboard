<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Binance implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('binance-balance');
    }

    /**
     * Data to be broadcasted.
     */
    public function broadcastWith(): array
    {
        return ['balance' => $this->data];
    }

    /**
     * Name of the event to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'balance.update';
    }
}
