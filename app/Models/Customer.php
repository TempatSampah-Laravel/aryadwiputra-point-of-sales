<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'no_telp',
        'address',
        'province_id',
        'province_name',
        'regency_id',
        'regency_name',
        'district_id',
        'district_name',
        'village_id',
        'village_name',
    ];

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function customerCredits()
    {
        return $this->hasMany(CustomerCredit::class);
    }
}
