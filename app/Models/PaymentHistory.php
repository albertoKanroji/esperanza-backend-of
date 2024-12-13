<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentHistory extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'payment_history';

    // Columnas que se pueden asignar masivamente
    protected $fillable = [
        'user_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_status',
        'encrypted_card_details',
        'container_name'
    ];

    // Atributos ocultos para serialización
    protected $hidden = [
        'encrypted_card_details'
    ];

    // Método para encriptar datos sensibles antes de guardarlos
    public function setEncryptedCardDetailsAttribute($value)
    {
        $this->attributes['encrypted_card_details'] = Crypt::encryptString($value);
    }

    // Método para desencriptar datos sensibles al acceder a ellos
    public function getEncryptedCardDetailsAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
