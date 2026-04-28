<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'currency_id',
        'user_id',
        'amount',
        'status',
        'failure_reason',
        'idempotency_key',
        'deleted_by',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(WithdrawalItem::class);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}