<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;
    protected $table = 'almacen';

    protected $fillable = [
        'nombre'
    ];

    public function entradas()
    {
        return $this->hasMany(Entrada::class);
    }
}
