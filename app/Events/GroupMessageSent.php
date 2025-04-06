<?php

namespace App\Events;

use App\Models\User;
use App\Models\GroupMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $from, public string $message, public int $groupid, public int $messageid)
    {
        //
    }

    public function broadcastOn()
    {
        return new PresenceChannel('group');
    }

    public function broadcastWith()
    {
        return [
            'sender_id'   => $this->from->id,
            'sender_name' => $this->from->name, // Adding sender's name
            'image'      => $this->from->image ?? '/images/avatar.svg', // Default avatar if null
            'message'     => $this->message,
            'group_id'    => $this->groupid,
            'message_id'  => $this->messageid,
            'timestamp'   => now()->format('h:i A'), // Formatted timestamp
        ];
    }
}
