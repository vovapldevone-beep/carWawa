<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZoneController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders', function () {
    return view('ordersPage');
});
