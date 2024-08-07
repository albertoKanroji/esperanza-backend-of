<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';

    protected $fillable = [
        'users_id',
        'users_id1',
        'contenido'
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
