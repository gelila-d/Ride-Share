<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TripController;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',[LoginController::class,'submit']);
Route::post('/login/verify', [LoginController::class,'verify']);

Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::get('/driver', [DriverController::class,'show']);
    Route::post('/driver', [DriverController::class,'update']);

    Route::post('/trip', [TripController::class,'store']);
Route::get('/trips/{trip}', [TripController::class,'show']);

Route::post('/trips/{trip}/accept', [TripController::class,'accept']);
Route::post('/trips/{trip}/start', [TripController::class,'start']);
Route::post('/trips/{trip}/end', [TripController::class,'end']);
Route::post('/trips/{trip}/location', [TripController::class,'location']);
});



