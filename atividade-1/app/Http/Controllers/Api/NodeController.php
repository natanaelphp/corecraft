<?php

namespace App\Http\Controllers\Api;

use App\Services\BitcoinRPC;
use App\Http\Controllers\Controller;

class NodeController extends Controller
{
    public function __invoke(BitcoinRPC $rpc)
    {
        $bc = $rpc->call('getblockchaininfo');
        $mp = $rpc->call('getmempoolinfo');
        $nw = $rpc->call('getnetworkinfo');

        $data = [
            'chain' => $bc['chain'],
            'blocks' => $bc['blocks'],
            'headers' => $bc['headers'],
            'difficulty' => $bc['difficulty'],
            'bestblockhash' => $bc['bestblockhash'],
            'mempool' => [
                'txcount' => $mp['size'],
                'bytes' => $mp['bytes'],
                'usage' => $mp['usage'],
                'maxmempool' => $mp['maxmempool'],
                'mempoolminfee' => $mp['mempoolminfee'],
            ],
            'network' => [
                'subversion' => $nw['subversion'],
                'connections' => $nw['connections'],
                'version' => $nw['version'],
            ],
        ];

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
