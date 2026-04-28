<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtmCashBalance extends Model
{
    protected $fillable = [
        'denomination_id',
        'quantity',
    ];

    public function denomination()
    {
        return $this->belongsTo(Denomination::class);
    }
}