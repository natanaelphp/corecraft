<?php

namespace App\Http\Controllers\Api;

use App\Services\BitcoinRPC;
use App\Http\Controllers\Controller;

class BlockchainController extends Controller
{
    public function __invoke(BitcoinRPC $rpc)
    {
        $info = $rpc->call('getblockchaininfo');

        return response()->json([
            'ok' => true,
            'data' => [
                'blocks' => $info['blocks'],
                'headers' => $info['headers'],
                'lag' => $info['headers'] - $info['blocks'],
            ],
        ]);
    }
}
