<?php
// Replace 'IpayController' with your actual controller name

// Routes for Views
Route::group(['middleware' => ['auth']], function () {
    Route::get('/ipay', 'IpayController@index');
    Route::post('/ipay/payment_ipay_checker', 'IpayController@paymentIpayChecker');
    Route::get('/ipay/payment_ipay_return', 'IpayController@paymentIpayReturn');
    Route::get('/ipay/payment_ipay_notify', 'IpayController@paymentIpayNotify');

});
