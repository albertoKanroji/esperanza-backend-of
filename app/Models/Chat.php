<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['sender_id', 'receiver_id'];

    // Relación con el primer usuario
    public function user1()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relación con el segundo usuario
    public function user2()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Relación con los mensajes del chat (si quieres rastrear mensajes por chat)
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}