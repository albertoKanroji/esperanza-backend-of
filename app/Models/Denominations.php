<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denominations extends Model
{
    use HasFactory;

    protected $fillable = ['type','value','image'];

}
