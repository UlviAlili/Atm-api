<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    public function denominations()
    {
        return $this->hasMany(Denomination::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
}