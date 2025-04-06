<?php

namespace App\Events;

use App\Models\User;
use App\Models\Group;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUserSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user, public Group $group)
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
            new PresenceChannel('groupUser'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'user_id'   => $this->user->id,
            'user_name' => $this->user->name, // Adding sender's name
            'avatar'      => $this->user->image ?? '/images/avatar.svg', // Default avatar if null
            'group_id'    => $this->group->id,
            'timestamp'   => now()->format('h:i A'), // Formatted timestamp
        ];
    }
}
