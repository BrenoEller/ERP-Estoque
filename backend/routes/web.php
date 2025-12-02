<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CompraController;

Route::get('/', function () {
    return view('welcome');
});

Route::withoutMiddleware('web')->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::get('/produtos', 'index');
        Route::post('/produtos', 'store');
        Route::put('/produtos/{id}', 'update');
    });

    Route::controller(PurchaseController::class)->group(function () {
        Route::get('/compras', 'index');
        Route::post('/compras', 'store');
    });

    Route::controller(SaleController::class)->group(function () {
        Route::get('/vendas', 'index');
        Route::post('/vendas', 'store');
        Route::post('/vendas/{id}/cancelar', 'cancelar');
    });
});