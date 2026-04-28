<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::query()
            ->with('currency')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $accounts,
        ]);
    }

    public function show(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }

        $account->load('currency');

        return response()->json([
            'data' => $account,
        ]);
    }
}