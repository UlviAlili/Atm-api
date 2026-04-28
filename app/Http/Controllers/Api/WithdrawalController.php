<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Withdrawal;
use App\Services\AtmWithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    private $atmWithdrawalService;

    public function __construct(AtmWithdrawalService $atmWithdrawalService)
    {
        $this->atmWithdrawalService = $atmWithdrawalService;
    }

    public function index(Request $request)
    {
        $withdrawals = Withdrawal::query()
            ->with([
                'account',
                'currency',
                'items.denomination',
            ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($withdrawals);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ]);

        $withdrawal = $this->atmWithdrawalService->withdraw(
            $request->user(),
            $request->account_id,
            $request->amount,
            $request->idempotency_key
        );

        if ($withdrawal->status === 'failed') {
            return response()->json([
                'message' => 'Withdrawal failed',
                'reason' => $withdrawal->failure_reason,
                'data' => $withdrawal,
            ], 422);
        }

        return response()->json([
            'message' => 'Withdrawal completed successfully',
            'data' => $withdrawal,
        ], 201);
    }

    public function show(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $withdrawal->load([
            'account',
            'currency',
            'items.denomination',
        ]);

        return response()->json($withdrawal);
    }

    public function destroy(Request $request, Withdrawal $withdrawal)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only admin can delete withdrawals.');
        }

        $oldValues = $withdrawal->toArray();

        $withdrawal->deleted_by = $request->user()->id;
        $withdrawal->save();

        $withdrawal->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'withdrawal_deleted',
            'model_type' => Withdrawal::class,
            'model_id' => $withdrawal->id,
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Withdrawal deleted successfully',
        ]);
    }
}