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

Route::get('test-send', function() {
    return 'Test route works';
});

Route::post('transactions/send', [TransactionsController::class, 'send']);

Route::post('transactions/receive/{bank}', [TransactionsController::class, 'receive'])
    ->where('bank', 'foodics|acme');

