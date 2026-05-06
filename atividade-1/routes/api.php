<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodeController;
use App\Http\Controllers\Api\BlockController;

Route::get('node', NodeController::class);
Route::get('blocks/recent', [BlockController::class, 'recent']);
Route::get('block/{hash}', [BlockController::class, 'show']);
