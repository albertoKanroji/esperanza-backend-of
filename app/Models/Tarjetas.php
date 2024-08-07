<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarjetas extends Model
{
    use HasFactory;
    protected $table = 'tarjetas';

    protected $fillable = [
        'number',
        'year',
        'mes',
        'cvv',

    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_tarjetas', 'tarjetas_id', 'users_id');
    }
}
