<?php

namespace App\Http\Controllers\Api;

use App\Services\BitcoinRPC;
use App\Http\Controllers\Controller;

class MempoolController extends Controller
{
    public function __invoke(BitcoinRPC $rpc)
    {
        $info = $rpc->call('getmempoolinfo');
        $transactions = $rpc->call('getrawmempool', [true]);

        $fees = collect($transactions)->map(function ($transaction) {
            return $transaction['fees']['base'] * 100_000_000 / $transaction['vsize'];
        });

        $data = [
            'tx_count' => $info['size'],
            'total_vsize' => $info['bytes'],
            'avg_fee_rate' => $fees->avg(),
            'min_fee_rate' => $fees->min(),
            'max_fee_rate' => $fees->max(),
            'fee_distribution' => [
                'low' => $fees->filter(fn ($fee) => $fee < 10)->count(),
                'medium' => $fees->filter(fn ($fee) => $fee >= 10 && $fee <= 50)->count(),
                'high' => $fees->filter(fn ($fee) => $fee > 50)->count(),
            ],
        ];

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
