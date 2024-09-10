<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contenedor extends Model
{
    use HasFactory;

    // Especifica la conexión que utilizará este modelo
    protected $connection = 'mysql_second';

    // Especifica el nombre de la tabla si no sigue la convención plural
    protected $table = 'patio_estadia';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'folio';

    // Indica si la clave primaria es un incremento automático
    public $incrementing = true; // Cambia a true si 'folio' es autoincremental

    // Especifica el tipo de datos de la clave primaria
    protected $keyType = 'int'; // Cambia a 'int' si la clave primaria es un entero

    // Especifica los atributos que se pueden asignar masivamente
    protected $fillable = [
        'folio',
        'id_patio',
        'no_contenedor',
        'sello',
        'f_ingreso',
        'f_salida',
        'posicion',
        'ct',
        'tarifa',
        'id_cliente',
        'estado',
        'no_despacho',
        'estadia',
        'fila',
        'nivel',
        'pedimento',
        'color',
        'tipo_contenedor',
        'id_ingreso',
        'id_salida',
        'id_detalle_s',
        'id_detalle_i',
        'ingreso',
        'salida',
        '_key',
        'observaciones',
        'ubicacion',
        '_pagado',
    ];

    // Si los campos de tiempo (created_at y updated_at) no están en la tabla, desactiva los timestamps
    public $timestamps = false;
}
