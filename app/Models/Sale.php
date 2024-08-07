<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['total','items','cash','change','status','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }




    // MUTATORS
    /*
    public function setTotalAttribute($value)
    {
        $priceBeforeSave = $this->attributes['total'];

        $priceFilter  = filter_var($priceBeforeSave, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $this->attributes['total'] = $priceFilter;
    }
    */

}
