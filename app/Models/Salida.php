<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    use HasFactory;
    protected $table = 'salidas';

    protected $fillable = [
        'users_id',
        'products_id',
        'clientes_id',
        'camion_id',
    ];

    public function trabajador()
    {
        return $this->belongsTo(User::class, 'users_id')->where('profile', 'EMPLOYEE');
    }

    public function producto()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'clientes_id')->where('profile', 'CLIENT');
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class, 'camion_id');
    }
}
