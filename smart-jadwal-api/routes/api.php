<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KegiatanController;

Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout']);

// Kelas Routes
Route::apiResource('kelas', KelasController::class);

// Guru Routes
Route::apiResource('guru', GuruController::class);

// Mapel Routes
Route::apiResource('mapel', MapelController::class);

// Mapel-Guru Routes
Route::post('/mapel/{mapelId}/guru/{guruId}', [MapelController::class, 'attachGuru']);
Route::delete('/mapel/{mapelId}/guru/{guruId}', [MapelController::class, 'detachGuru']);

// Jadwal Routes - custom routes before apiResource
Route::get('/jadwal/kelas/{kelasId}', [JadwalController::class, 'getByKelas']);
Route::apiResource('jadwal', JadwalController::class);

// Kegiatan Routes
Route::apiResource('kegiatan', KegiatanController::class);