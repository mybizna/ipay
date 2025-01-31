<?php
// Replace 'IpayController' with your actual controller name
use Illuminate\Support\Facades\Route;
use Modules\Ipay\Http\Controllers\IpayController;

// Routes for Views
Route::group(['middleware' => ['auth']], function () {
    Route::get('/ipay', [IpayController::class, 'index']);
    Route::post('/ipay/payment_ipay_checker', [IpayController::class, 'paymentIpayChecker']);
    Route::get('/ipay/payment_ipay_return', [IpayController::class, 'paymentIpayReturn']);
    Route::get('/ipay/payment_ipay_notify', [IpayController::class, 'paymentIpayNotify']);

});
