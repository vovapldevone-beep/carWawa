<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZoneController;

Route::get('/zones', [ZoneController::class, 'index']);

Route::post('/zones', [ZoneController::class, 'store']);