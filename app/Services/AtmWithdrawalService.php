<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AtmCashBalance;
use App\Models\AuditLog;
use App\Models\Withdrawal;
use App\Models\WithdrawalItem;
use Illuminate\Support\Facades\DB;
use Exception;

class AtmWithdrawalService
{
    public function withdraw($user, int $accountId, int $amount, ?string $idempotencyKey = null)
    {
        return DB::transaction(function () use ($user, $accountId, $amount, $idempotencyKey) {

            if ($idempotencyKey) {
                $existingWithdrawal = Withdrawal::where('idempotency_key', $idempotencyKey)->first();

                if ($existingWithdrawal) {
                    return $existingWithdrawal->load('items.denomination');
                }
            }

            $account = Account::where('id', $accountId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            if ($account->balance < $amount) {
                return $this->createFailedWithdrawal(
                    $account,
                    $user,
                    $amount,
                    $idempotencyKey,
                    'Insufficient account balance'
                );
            }

            $cashBalances = AtmCashBalance::query()
                ->select('atm_cash_balances.*')
                ->join('denominations', 'denominations.id', '=', 'atm_cash_balances.denomination_id')
                ->where('denominations.currency_id', $account->currency_id)
                ->where('denominations.is_active', true)
                ->where('atm_cash_balances.quantity', '>', 0)
                ->with('denomination')
                ->orderByDesc('denominations.value')
                ->lockForUpdate()
                ->get();

            $combination = $this->findBestCombination($amount, $cashBalances);

            if (!$combination) {
                return $this->createFailedWithdrawal(
                    $account,
                    $user,
                    $amount,
                    $idempotencyKey,
                    'ATM has no suitable banknote combination'
                );
            }

            $account->balance -= $amount;
            $account->save();

            $withdrawal = Withdrawal::create([
                'account_id' => $account->id,
                'currency_id' => $account->currency_id,
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'success',
                'failure_reason' => null,
                'idempotency_key' => $idempotencyKey,
            ]);

            foreach ($combination as $denominationId => $quantity) {
                $cashBalance = $cashBalances->firstWhere('denomination_id', $denominationId);

                $cashBalance->quantity -= $quantity;
                $cashBalance->save();

                WithdrawalItem::create([
                    'withdrawal_id' => $withdrawal->id,
                    'denomination_id' => $denominationId,
                    'quantity' => $quantity,
                ]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'withdrawal_created',
                'model_type' => Withdrawal::class,
                'model_id' => $withdrawal->id,
                'old_values' => null,
                'new_values' => [
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'status' => 'success',
                    'banknotes' => $combination,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $withdrawal->load('items.denomination');
        });
    }

    private function createFailedWithdrawal($account, $user, int $amount, ?string $idempotencyKey, string $reason)
    {
        $withdrawal = Withdrawal::create([
            'account_id' => $account->id,
            'currency_id' => $account->currency_id,
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => 'failed',
            'failure_reason' => $reason,
            'idempotency_key' => $idempotencyKey,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'withdrawal_failed',
            'model_type' => Withdrawal::class,
            'model_id' => $withdrawal->id,
            'old_values' => null,
            'new_values' => [
                'account_id' => $account->id,
                'amount' => $amount,
                'status' => 'failed',
                'reason' => $reason,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $withdrawal;
    }

    private function findBestCombination(int $amount, $cashBalances): ?array
    {
        $dp = [
            0 => [
                'count' => 0,
                'items' => [],
            ],
        ];

        foreach ($cashBalances as $cashBalance) {
            $denominationId = $cashBalance->denomination_id;
            $value = (int) $cashBalance->denomination->value;
            $availableQuantity = (int) $cashBalance->quantity;

            $currentDp = $dp;

            foreach ($currentDp as $currentAmount => $data) {
                $maxQuantity = min($availableQuantity, intdiv($amount - $currentAmount, $value));

                for ($quantity = 1; $quantity <= $maxQuantity; $quantity++) {
                    $newAmount = $currentAmount + ($value * $quantity);
                    $newCount = $data['count'] + $quantity;

                    if (
                        !isset($dp[$newAmount]) ||
                        $newCount < $dp[$newAmount]['count']
                    ) {
                        $items = $data['items'];
                        $items[$denominationId] = ($items[$denominationId] ?? 0) + $quantity;

                        $dp[$newAmount] = [
                            'count' => $newCount,
                            'items' => $items,
                        ];
                    }
                }
            }
        }

        return $dp[$amount]['items'] ?? null;
    }
}