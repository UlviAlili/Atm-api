<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::query()
            ->where('is_active', true)
            ->get();

        return response()->json([
            'data' => $currencies,
        ]);
    }

    public function show(Currency $currency)
    {
        $currency->load('denominations');

        return response()->json([
            'data' => $currency,
        ]);
    }
}