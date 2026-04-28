<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AtmCashBalance;
use App\Models\Currency;
use App\Models\Denomination;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AtmSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $azn = Currency::create([
            'code' => 'AZN',
            'name' => 'Azerbaijani Manat',
            'is_active' => true,
        ]);

        $usd = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'is_active' => true,
        ]);

        $aznDenominations = [200, 100, 50, 20, 10, 5];
        $usdDenominations = [100, 50, 20, 10, 5, 1];

        foreach ($aznDenominations as $value) {
            $denomination = Denomination::create([
                'currency_id' => $azn->id,
                'value' => $value,
                'is_active' => true,
            ]);

            AtmCashBalance::create([
                'denomination_id' => $denomination->id,
                'quantity' => 20,
            ]);
        }

        foreach ($usdDenominations as $value) {
            $denomination = Denomination::create([
                'currency_id' => $usd->id,
                'value' => $value,
                'is_active' => true,
            ]);

            AtmCashBalance::create([
                'denomination_id' => $denomination->id,
                'quantity' => 20,
            ]);
        }

        Account::create([
            'user_id' => $user->id,
            'currency_id' => $azn->id,
            'account_number' => 'AZN-000001',
            'balance' => 250,
            'is_active' => true,
        ]);

        Account::create([
            'user_id' => $user->id,
            'currency_id' => $usd->id,
            'account_number' => 'USD-000001',
            'balance' => 500,
            'is_active' => true,
        ]);
    }
}