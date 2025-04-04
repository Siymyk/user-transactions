<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/users', [UserController::class, 'createUser']);
Route::post('/users/{id}/deposit', [UserController::class, 'deposit']);
Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);
Route::get('/users/{id}/transactions', [TransactionController::class, 'getUserTransactions']);
