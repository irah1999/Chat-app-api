<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    use HasFactory;
    protected $fillable = ['group_id', 'user_id', 'message', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
