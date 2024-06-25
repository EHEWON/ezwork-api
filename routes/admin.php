<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\SettingController;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/users', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'info'])->where('id','[0-9]+');
Route::post('/user/{id}', [UserController::class, 'edit'])->where('id','[0-9]+');
Route::get('/customers', [CustomerController::class, 'index']);
Route::get('/customer/{id}', [CustomerController::class, 'info'])->where('id','[0-9]+');
Route::post('/customer/{id}', [CustomerController::class, 'edit'])->where('id','[0-9]+');
Route::post('/customer/status/{id}', [CustomerController::class, 'status'])->where('id','[0-9]+');

Route::get('/translates', [TranslateController::class, 'index']);
Route::delete('/translate/{id}', [TranslateController::class, 'delete']);

Route::post('/image', [ImageController::class, 'index']);


Route::get('/setting/notice', [SettingController::class, 'notice']);
Route::post('/setting/notice', [SettingController::class, 'notice_setting']);