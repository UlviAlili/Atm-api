<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Denomination;
use Illuminate\Http\Request;

class DenominationController extends Controller
{
    public function index(Request $request)
    {
        $denominations = Denomination::query()
            ->with('currency')
            ->where('is_active', true)
            ->when($request->currency_id, function ($query) use ($request) {
                $query->where('currency_id', $request->currency_id);
            })
            ->orderByDesc('value')
            ->get();

        return response()->json([
            'data' => $denominations,
        ]);
    }

    public function show(Denomination $denomination)
    {
        $denomination->load('currency');

        return response()->json([
            'data' => $denomination,
        ]);
    }
}