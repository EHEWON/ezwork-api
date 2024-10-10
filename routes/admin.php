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
Route::post('/translates/delete', [TranslateController::class, 'deleteMore']);
Route::post('/translates/download', [TranslateController::class, 'downloadMore']);

Route::post('/image', [ImageController::class, 'index']);


Route::get('/setting/notice', [SettingController::class, 'notice']);
Route::post('/setting/notice', [SettingController::class, 'notice_setting']);

Route::get('/setting/api', [SettingController::class, 'get_api']);
Route::post('/setting/api', [SettingController::class, 'set_api']);

Route::get('/setting/other', [SettingController::class, 'get_other']);
Route::post('/setting/other', [SettingController::class, 'set_other']);

Route::get('/setting/site', [SettingController::class, 'get_site']);
Route::post('/setting/site', [SettingController::class, 'set_site']);
