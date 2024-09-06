<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Exception;

class Tarjetas extends Model
{
    use HasFactory;

    protected $table = 'tarjetas';

    protected $fillable = [
        'token',
        'number',
        'year',
        'mes',
        'cvv',
        'titular',
    ];

    // Define many-to-many relationship with User model
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_tarjetas', 'tarjetas_id', 'users_id');
    }

    // Automatically encrypt the card number when saving
    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = Crypt::encryptString($value);
    }

    // Automatically decrypt the card number when accessing it
    public function getNumberAttribute($value)
    {
        try {
            return Crypt::decryptString($value);  // Attempt to decrypt
        } catch (Exception $e) {
            return $value;  // Return encrypted value if decryption fails
        }
    }

    // Automatically encrypt the CVV when saving
    public function setCvvAttribute($value)
    {
        $this->attributes['cvv'] = Crypt::encryptString($value);
    }

    // Automatically decrypt the CVV when accessing it
    public function getCvvAttribute($value)
    {
        try {
            return Crypt::decryptString($value);  // Attempt to decrypt
        } catch (Exception $e) {
            return $value;  // Return encrypted value if decryption fails
        }
    }
}
