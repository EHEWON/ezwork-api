<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/send', [AuthController::class, 'sendByRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/find/send', [AuthController::class, 'sendByFind']);
Route::post('/find', [AuthController::class, 'find']);
