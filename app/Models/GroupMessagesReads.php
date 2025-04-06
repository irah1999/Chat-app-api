<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessagesReads extends Model
{
    use HasFactory;
    protected $fillable = ['message_id', 'group_id', 'user_id', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
