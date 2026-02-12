<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('transactions/foodics/receive', [TransactionsController::class, 'receive'])->defaults('bank','foodics');
Route::post('transactions/acme/receive', [TransactionsController::class, 'receive'])->defaults('bank','acme');

Route::post('transactions/send', [TransactionsController::class, 'send']);
