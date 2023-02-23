<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LoansController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login')->name('login');
});
Route::middleware('auth:sanctum')->group( function () {
    Route::get('logout', [AuthController::class,'logout'])->name('logout');
    Route::post('loan', [LoansController::class, 'create']);
    Route::put('loan/{loan}/approval', [LoansController::class,'approve'])->middleware('role:ADMIN');
    Route::get('user/{user}/loans', [LoansController::class,'userLoan']);
    Route::get('loans', [LoansController::class,'loanList']);
    Route::put('loan/{loan}/repayment', [LoansController::class, 'repayment']);
});
        

