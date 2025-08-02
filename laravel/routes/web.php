<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/line', function () {
    return response()->json(['message' => 'line base is working']);
});
Route::prefix('line')->group(function () {
    Route::get('/status', function () {
        return view('line.status');
    });
    Route::POST('/merge', [LineController::class, 'merge']);
    Route::prefix('get')->group(function () {
        Route::POST('/profile', [LineController::class, 'merge']);
    });
});