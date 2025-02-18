<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\TranslateController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\ComparisonController;
use App\Http\Controllers\Api\PromptController;

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
Route::post('/check/doc2x', [TranslateController::class, 'check_doc2x']);
Route::get('/storage', [AccountController::class, 'storage']);
Route::get('/info', [AccountController::class, 'info']);

// 术语表
Route::get('/comparison/my', [ComparisonController::class, 'my']);
Route::get('/comparison/share', [ComparisonController::class, 'share']);
Route::post('/comparison/{id}', [ComparisonController::class, 'edit'])->where('id','[0-9]+');
Route::post('/comparison/share/{id}', [ComparisonController::class, 'edit_share'])->where('id','[0-9]+');
Route::post('/comparison/copy/{id}', [ComparisonController::class, 'copy'])->where('id','[0-9]+');
Route::post('/comparison/fav/{id}', [ComparisonController::class, 'fav'])->where('id','[0-9]+');
Route::post('/comparison', [ComparisonController::class, 'add']);
Route::delete('/comparison/{id}', [ComparisonController::class, 'del'])->where('id','[0-9]+');

Route::get('/comparison/template', [ComparisonController::class, 'template']);
Route::post('/comparison/import', [ComparisonController::class, 'import']);
Route::get('/comparison/export/{id}', [ComparisonController::class, 'export'])->where('id','[0-9]+');
Route::get('/comparison/export/all', [ComparisonController::class, 'exportAll']);

//提示语
Route::get('/prompt/my', [PromptController::class, 'my']);
Route::get('/prompt/share', [PromptController::class, 'share']);
Route::post('/prompt/{id}', [PromptController::class, 'edit'])->where('id','[0-9]+');
Route::post('/prompt/share/{id}', [PromptController::class, 'edit_share'])->where('id','[0-9]+');
Route::post('/prompt/copy/{id}', [PromptController::class, 'copy'])->where('id','[0-9]+');
Route::post('/prompt/fav/{id}', [PromptController::class, 'fav'])->where('id','[0-9]+');
Route::post('/prompt', [PromptController::class, 'add']);
Route::delete('/prompt/{id}', [PromptController::class, 'del'])->where('id','[0-9]+');


Route::get('/common/setting', [CommonController::class, 'setting']);
Route::get('/common/all_settings', [CommonController::class, 'getAllSettings']);
