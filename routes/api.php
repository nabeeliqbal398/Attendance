<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CapacitorDataController;
use App\Http\Controllers\Api\RegionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'throttle:api']);

// Region (administrative areas) Data Endpoints
Route::prefix('regions')->group(function () {
    Route::get('/provinces', [RegionController::class, 'provinces']);
    Route::get('/districts/{provinceCode}', [RegionController::class, 'districts']);
    Route::get('/subdistricts/{districtCode}', [RegionController::class, 'subdistricts']);
    Route::get('/villages/{subdistrictCode}', [RegionController::class, 'villages']);
});

// Capacitor Device API Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('device')->group(function () {
    Route::post('/location', [CapacitorDataController::class, 'getLocation']);
    Route::post('/barcode', [CapacitorDataController::class, 'saveBarcodeData']);
    Route::post('/photo', [CapacitorDataController::class, 'uploadPhoto']);
    Route::get('/permissions', [CapacitorDataController::class, 'getPermissionsStatus']);
});
