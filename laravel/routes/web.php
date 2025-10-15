<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth; // นำเข้า Auth facade

// Route สำหรับหน้าหลักของเว็บไซต์ ที่ได้รับการป้องกัน
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $user = Auth::user();
        // ส่งข้อมูลผู้ใช้ไปยัง view 'index'
        return view('index', ['user' => $user]);
    });
});
// Route สำหรับหน้า Login และการล็อกอิน
Route::get('/auth/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

// API Routes ที่ไม่ต้องใช้ Session หรือ CSRF
Route::get('/line', function () {
    return response()->json(['message' => 'line base is working']);
});
Route::prefix('line')->group(function () {
    Route::get('/status', function () {
        return view('line.status');
    });
    Route::post('/merge', [LineController::class, 'merge']);
    Route::prefix('get')->group(function () {
        Route::post('/profile', [LineController::class, 'profile']);
    });
});