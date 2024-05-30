<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\TranslateController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/send', [AuthController::class, 'sendByRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/find/send', [AuthController::class, 'sendByFind']);
Route::post('/find', [AuthController::class, 'find']);
Route::post('/change', [AccountController::class, 'changePwd']);
Route::post('/upload', [UploadController::class, 'index']);
Route::post('/delFile', [UploadController::class, 'del']);
Route::get('/translates', [TranslateController::class, 'index']);
Route::post('/translate', [TranslateController::class, 'start']);
Route::delete('/translate/{id}', [TranslateController::class, 'del'])->where('id','[0-9]+');
Route::delete('/translate/all', [TranslateController::class, 'delAll']);
Route::post('/process', [TranslateController::class, 'process']);
Route::post('/check', [TranslateController::class, 'check']);
Route::get('/storage', [AccountController::class, 'storage']);
