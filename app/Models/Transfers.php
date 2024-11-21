<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfers extends Model
{
    use HasFactory;

    protected $fillable = ['id_sender', 'id_receiver', 'amount', 'description', 'status'];
    // protected $fillable = ['sender_id', 'receiver_id', 'content', 'chat_id'];

    // Relación con el usuario que envía el mensaje
    public function sender()
    {
        return $this->belongsTo(User::class, 'id_sender');
    }

    // Relación con el usuario que recibe el mensaje
    public function receiver()
    {
        return $this->belongsTo(User::class, 'id_receiver');
    }
}