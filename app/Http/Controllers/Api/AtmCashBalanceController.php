<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtmCashBalance;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AtmCashBalanceController extends Controller
{
    public function index(Request $request)
    {
        $cashBalances = AtmCashBalance::query()
            ->with([
                'denomination.currency'
            ])
            ->get()
            ->groupBy(function ($cashBalance) {
                return $cashBalance->denomination->currency->code;
            });

        return response()->json([
            'data' => $cashBalances,
        ]);
    }

    public function update(Request $request, AtmCashBalance $atmCashBalance)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only admin can update ATM cash balance.');
        }

        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $oldValues = $atmCashBalance->toArray();

        $atmCashBalance->update([
            'quantity' => $request->quantity,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'atm_cash_balance_updated',
            'model_type' => AtmCashBalance::class,
            'model_id' => $atmCashBalance->id,
            'old_values' => $oldValues,
            'new_values' => $atmCashBalance->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'ATM cash balance updated successfully',
            'data' => $atmCashBalance->fresh()->load('denomination.currency'),
        ]);
    }
}