<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'chats';

    protected $fillable = [
        'users_id',
        'users_id1'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'users_id1');
    }
}
