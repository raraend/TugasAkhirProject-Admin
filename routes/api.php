<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\ContentApiController;
use App\Http\Controllers\Api\MonitorContentApiController;
use App\Http\Controllers\Api\AuthApiController;

// Login & Logout
Route::post('/login', [AuthApiController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthApiController::class, 'logout']);

//  Akses File — Semua yang sudah login boleh akses, akan divalidasi di controllernya
// Route::middleware('auth:sanctum')->get('/file/{uuid}', [ContentApiController::class, 'serveFile']);

//  Akses Admin — Untuk ambil konten dan monitor_contents
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/contents', [ContentApiController::class, 'index']);
    Route::get('/monitor-contents', [MonitorContentApiController::class, 'index']);
    Route::get('/departments', [DepartmentApiController::class, 'index']);
});

Route::get('/sync-file/{uuid}', [ContentApiController::class, 'serveSyncFile']);
