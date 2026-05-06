<?php

use App\Services\BitcoinRPC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('node', function (BitcoinRPC $rpc) {
    $bc = $rpc->call('getblockchaininfo');
    $mp = $rpc->call("getmempoolinfo");
    $nw = $rpc->call("getnetworkinfo");

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
        ]
    ];

    return response()->json([
        'ok' => true,
        'data' => $data,
    ]);
});

Route::get('blocks/recent', function (BitcoinRPC $rpc, Request $request) {
    $numberOfBlocksRequested = (int) $request->input('n', 10);
    if ($numberOfBlocksRequested > 25) $numberOfBlocksRequested = 25;

    $blockCount = $rpc->call('getblockcount');

    $stop = max($blockCount - $numberOfBlocksRequested + 1, 0);

    $blocks = [];

    foreach (range($blockCount, $stop) as $height) {
        $blockHash = $rpc->call('getblockhash', [$height]);
        $blockHeader = $rpc->call('getblockheader', [$blockHash]);
        $stats = $rpc->call('getblockstats', [$blockHash]);

        $blocks[] = [
            'height' => $height,
            'hash' => $blockHash,
            'time' => $blockHeader['time'],
            'mediantime' => $blockHeader['mediantime'],
            'txs' => $stats['txs'],
            'totalfee' => $stats['totalfee'],
            'avgfee' => $stats['avgfee'],
            'avgfeerate' => $stats['avgfeerate'],
            'feerate_percentiles' => $stats['feerate_percentiles'],
            'avg_tx_size' => $stats['avgtxsize'],
            'total_size' => $stats['total_size'],
        ];
    }

    return response()->json([
        'ok' => true,
        'data' => [
            'items' => $blocks,
            'tip' => $blockCount,
        ],
    ]);
});

Route::get('block/{hash}', function (BitcoinRPC $rpc, Request $request, $hash) {
    $block = $rpc->call('getblock', [$hash, 1]);

    $data = [
        'hash' => $block['hash'],
        'height' => $block['height'],
        'confirmations' => $block['confirmations'],
        'time' => $block['time'],
        'nTx' => $block['nTx'],
        'size' => $block['size'],
        'weight' => $block['weight'],
        'version' => $block['version'],
        'previousblockhash' => $block['previousblockhash'],
        'nextblockhash' => $block['nextblockhash'] ?? null,
        'tx' => $block['tx']
    ];

    return response()->json([
        'ok' => true,
        'data' => $data,
    ]);
});
