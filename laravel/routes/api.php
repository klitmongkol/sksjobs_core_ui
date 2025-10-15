<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\EmployeeController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::prefix('upload')->group(function () {
        Route::get('/payroll', [UploadController::class, 'payroll']);
        Route::post('/payroll', [UploadController::class, 'payroll']);
    });
    Route::prefix('employee')->group(function () {
        Route::post('/list', [EmployeeController::class, 'list']);
        Route::post('/add', [EmployeeController::class, 'add']);
    });
});
