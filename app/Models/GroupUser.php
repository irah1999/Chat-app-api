<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupUser extends Model
{
    use HasFactory;
    protected $fillable = ['group_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
