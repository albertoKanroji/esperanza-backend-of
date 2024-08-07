<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntradaProducto extends Model
{
    use HasFactory;
    protected $table = 'entrada_almacen';

    protected $fillable = [
        'products_id',
        'fecha_entrada',
        'trabajadores_id',
        'precio_dia',
        'total_deuda',
        'clientes_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajadores_id')->where('profile', 'EMPLOYEE');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'clientes_id')->where('profile', 'CLIENT');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'trabajadores_id')->where('profile', 'EMPLOYEE');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }
}