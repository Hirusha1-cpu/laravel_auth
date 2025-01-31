<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\RolesController;
use App\Http\Controllers\API\LeaveRequestController;
use App\Http\Controllers\API\LeaveRequestedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post("register",[AuthController::class,"register"]);
Route::post("login",[AuthController::class,"login"]);
Route::middleware("auth:sanctum")->group(function(){
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::post('/', [PostController::class, 'store']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
    });
    Route::prefix('roles')->group(function () {
        Route::get('/', [RolesController::class, 'index']);
        // Route::get('/{id}', [PostController::class, 'show']);
        Route::post('/', [RolesController::class, 'store']);
        // Route::put('/{id}', [PostController::class, 'update']);
        // Route::delete('/{id}', [PostController::class, 'destroy']);
    });

    Route::prefix('leave-requests')->group(function () {
        Route::get('/', [LeaveRequestedController::class, 'index']);
        Route::get('/{id}', [LeaveRequestedController::class, 'show']);
        Route::post('/', [LeaveRequestedController::class, 'store']);
        Route::put('/{id}', [LeaveRequestedController::class, 'update']);
        Route::delete('/{id}', [LeaveRequestedController::class, 'destroy']);
    });

   

});

