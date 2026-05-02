<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZoneController;

Route::get('/zones', [ZoneController::class, 'index']);
Route::get('/zones/{zone}', [ZoneController::class, 'show']);

Route::post('/zones', [ZoneController::class, 'store']);