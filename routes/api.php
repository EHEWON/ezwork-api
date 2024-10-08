<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\TranslateController;
use App\Http\Controllers\Api\CommonController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/send', [AuthController::class, 'sendByRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/find/send', [AuthController::class, 'sendByFind']);
Route::post('/find', [AuthController::class, 'find']);
Route::post('/change', [AccountController::class, 'changePwd']);
Route::post('/upload', [UploadController::class, 'index']);
Route::post('/delFile', [UploadController::class, 'del']);
Route::get('/translates', [TranslateController::class, 'index']);
Route::get('/translate/setting', [TranslateController::class, 'setting']);
Route::get('/translate/test', [TranslateController::class, 'test']);
Route::post('/translate', [TranslateController::class, 'start']);
Route::delete('/translate/{id}', [TranslateController::class, 'del'])->where('id','[0-9]+');
Route::delete('/translate/all', [TranslateController::class, 'delAll']);
Route::get('/translate/finish/count', [TranslateController::class, 'finishTotal']);
Route::get('/translate/download/all', [TranslateController::class, 'downloadAll']);
Route::post('/process', [TranslateController::class, 'process']);
Route::post('/check/openai', [TranslateController::class, 'check_openai']);
Route::post('/check/pdf', [TranslateController::class, 'check_pdf']);
Route::get('/storage', [AccountController::class, 'storage']);
Route::get('/info', [AccountController::class, 'info']);

Route::get('/common/setting', [CommonController::class, 'setting']);
