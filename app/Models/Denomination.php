<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denomination extends Model
{
    protected $fillable = [
        'currency_id',
        'value',
        'is_active',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function atmCashBalance()
    {
        return $this->hasOne(AtmCashBalance::class);
    }

    public function withdrawalItems()
    {
        return $this->hasMany(WithdrawalItem::class);
    }
}