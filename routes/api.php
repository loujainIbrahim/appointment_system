<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProviderAvailabilityController;

Route::apiResource('appointments', AppointmentController::class);
Route::apiResource('services', ServiceController::class);
Route::get('services/{service}/providers', [ServiceController::class, 'providers']);
Route::get(
    'providers/{provider}/available-dates',
    [ProviderAvailabilityController::class, 'availableDates']
);
Route::get('providers/{provider}/available-slots', [ProviderAvailabilityController::class, 'availableSlots']);
