<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodeController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\MempoolController;
use App\Http\Controllers\Api\TransactionController;

Route::get('node', NodeController::class);

Route::get('blocks/recent', [BlockController::class, 'recent']);
Route::get('block/{hash}', [BlockController::class, 'show']);

Route::get('tx/{txid}', TransactionController::class);

Route::get('mempool/summary', MempoolController::class);
