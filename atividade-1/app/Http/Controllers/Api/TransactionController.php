<?php

namespace App\Http\Controllers\Api;

use App\Services\BitcoinRPC;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function __invoke(string $txid, BitcoinRPC $rpc)
    {
        try {
            $tx = $rpc->call('getrawtransaction', [$txid]);
        } catch (\Throwable $th) {
            return [
                'ok' => false,
                'error' => [
                    'details' => $th->getMessage(),
                    'message' => 'Falha ao consultar tx. Dica: isso exige txindex=1, ou a tx precisa estar na mempool/wallet.',
                ],
            ];
        }

        $data = [
            'txid' => $tx['txid'],
            'hash' => $tx['hash'],
            'version' => $tx['version'],
            'size' => $tx['size'],
            'vsize' => $tx['vsize'],
            'weight' => $tx['weight'],
            'locktime' => $tx['locktime'],
            'vin' => $tx['vin'],
            'vout' => $tx['vout'],
            'confirmations' => $tx['confirmations'],  # pode não existir
            'blockhash' => $tx['blockhash'],
            'time' => $tx['time'],
            'blocktime' => $tx['blocktime'],
        ];

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
