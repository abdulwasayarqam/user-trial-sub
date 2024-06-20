<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', function () {
    return redirect('/');
});

Route::group(['middleware' => ['guest']], function(){

    Route::get('/register', [AuthController::class, 'loadRegister']);
    Route::post('/register', [AuthController::class, 'userRegister'])->name('userRegister');

    Route::get('/',[AuthController::class,'loadLogin']);
    Route::post('/',[AuthController::class,'userLogin'])->name('userLogin');

});

Route::group(['middleware' => ['userAuth']], function(){

    Route::get('/dashboard',[AuthController::class,'dashboard'])->name('dashboard');

});

Route::group(['middleware' => ['isAuthenticate']], function(){

    Route::get('/subscription',[SubscriptionController::class,'loadSubscription'])->name('subscription');
    Route::post('/get-plan-details', [SubscriptionController::class, 'getPlanDetails'])->name('getPlanDetails');

    Route::post('/logout', [AuthController::class,'logout'])->name('logout');

    Route::post('/create-subscription', [SubscriptionController::class,'createSubscription'])->name('createSubscription');
    Route::post('/cancel-subscription', [SubscriptionController::class,'cancelSubscription'])->name('cancelSubscription');
});

