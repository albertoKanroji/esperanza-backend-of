<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'name', 'barcode', 'cost', 'price', 'stock', 'alerts', 'image', 'category_id', 'estado_escaneo',
        'estado_deuda',
        'estadia'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_products');
    }
}
