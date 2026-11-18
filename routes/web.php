<?php

use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\WeighbridgeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeighbridgeController::class, 'index'])->name('weighbridge.index');

Route::post('/tickets/{ticket}/weigh', [WeighbridgeController::class, 'weigh'])
    ->name('tickets.weigh');

Route::post('/tickets/{ticket}/resolve-overload', [WeighbridgeController::class, 'resolveOverload'])
    ->name('tickets.resolve-overload');

Route::post('/tickets/{ticket}/dispatch', [WeighbridgeController::class, 'dispatch'])
    ->name('tickets.dispatch');

Route::post('/sales-orders/{salesOrder}/next-ticket', [SalesOrderController::class, 'createNextTicket'])
    ->name('sales-orders.next-ticket');
