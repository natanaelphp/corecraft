<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BitcoinRPC;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function recent(BitcoinRPC $rpc, Request $request)
    {
        $numberOfBlocksRequested = (int) $request->input('n', 10);

        if ($numberOfBlocksRequested > 25) {
            $numberOfBlocksRequested = 25;
        }

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
    }

    public function show(BitcoinRPC $rpc, string $hash)
    {
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
            'tx' => $block['tx'],
        ];

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }
}
