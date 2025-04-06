<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('group', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name, // Include necessary user details
    ];
});

Broadcast::channel('groupUser', function ($user) {
    return [
        'user' => $user,
    ];
});