<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalItem extends Model
{
    protected $fillable = [
        'withdrawal_id',
        'denomination_id',
        'quantity',
    ];

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function denomination()
    {
        return $this->belongsTo(Denomination::class);
    }
}