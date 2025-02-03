<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LeaveDetailsController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\RolesController;
use App\Http\Controllers\API\LeaveRequestController;
use App\Http\Controllers\API\LeaveRequestedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);
Route::middleware("auth:sanctum")->group(function () {
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
        Route::get('/user', [LeaveRequestedController::class, 'show']);
        Route::post('/', [LeaveRequestedController::class, 'store']);
        Route::put('/user/{postid}', [LeaveRequestedController::class, 'update']);
        Route::delete('/user/{postid}', [LeaveRequestedController::class, 'destroy']);
    });

    Route::prefix('leave-details')->group(function () {
        Route::get('/user', [LeaveDetailsController::class, 'getUserLeaveDetails']);
        Route::get('/all', [LeaveDetailsController::class, 'getAllUsersLeaveDetails']);
        ROute::get('/leaveDetails', [LeaveDetailsController::class, 'get_leave_details']);
    });
    Route::post('/approve-user/{id}', [AuthController::class, 'approveUser']);

    Route::get('leave-requests/user/{userId}/history', [LeaveRequestedController::class, 'getUserLeaveHistory']);
    Route::get('leave-requests/user/{userId}/stats', [LeaveRequestedController::class, 'getUserLeaveStats']);

    Route::prefix('managers')->group(function () {
        Route::get('/', [AuthController::class, 'getManagersDetails']);
    });
    Route::get('leave-requests/all-users', [LeaveRequestedController::class, 'getAllUsersLeaveHistory']);
});
