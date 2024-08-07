<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    use HasFactory;
    protected $table = 'entradas';

    protected $fillable = [
        'camion_id',
        'almacen_id',
        'products_id',
        'users_id',
        'total_deuda',
        'clientes_id'
    ];

    public function camion()
    {
        return $this->belongsTo(Camion::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id')->where('profile', 'EMPLOYEE');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'clientes_id')->where('profile', 'CLIENT');
    }
}
