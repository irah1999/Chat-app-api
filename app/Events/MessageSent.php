<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $receiver, public User $sender, public string $message)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->receiver->id}"),
        ];
    }

    public function broadcastWith()
    {
        return [
            'sender_id'   => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'sender_name' => $this->sender->name, // Adding sender's name
            'message'     => $this->message,
            'timestamp'   => now()->format('h:i A'), // Formatted timestamp
        ];
    }
}
