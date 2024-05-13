<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;

Route::get('/users', [UserController::class, 'index']);
Route::get('/user/{id}', [UserController::class, 'info'])->where('id','[0-9]+');
Route::post('/user/{id}', [UserController::class, 'edit'])->where('id','[0-9]+');
Route::get('/customers', [CustomerController::class, 'index']);
Route::get('/customer/detail/{id}', [CustomerController::class, 'detail'])->where('id','[0-9]+');
Route::post('/setting/notice', [SettingController::class, 'notice_setting']);
