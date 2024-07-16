<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PoliciesManagementController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\TimerController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\LeaveManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/  
Route::controller(AuthController::class)->group(function(){
    Route::post("signup","register");
    Route::post("login","login")->name('login');
    Route::get("token-check","checkToken")->name('token-check')->middleware('isBanned');
});
Route::middleware('auth:api')->group(function () {
    // Protected route to get the authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected route for logout
    Route::post('/update/profile', [AuthController::class, 'update_profile']);
    Route::get('get/profile', [AuthController::class, 'get_profile']);
    Route::get('get/profile', [AuthController::class, 'get_profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('upload-documents', [AuthController::class, 'upload_document']);

    // holiday 
    Route::post('add/holiday', [HolidayController::class, 'create']);
    Route::get('edit/holiday/{id}', [HolidayController::class, 'edit']);
    Route::get('get/holiday', [HolidayController::class, 'index']);
    Route::post('update/holiday', [HolidayController::class, 'update']);

    
    // Project data 
    Route::post('add/project', [ProjectController::class, 'create']);
    Route::get('edit/project/{id}', [ProjectController::class, 'edit']);
    Route::get('get/project', [ProjectController::class, 'index']);
    Route::post('update/project', [ProjectController::class, 'update']);

    // TIMERS  DATA
    Route::post('projects/timers/stop/{id}', [TimerController::class, 'stopRunning']);
    Route::post('projects/timers/store/{id}', [TimerController::class, 'store']);
    Route::get('projects/timers/active/{id}', [TimerController::class, 'running']);
    Route::get('projects/timers/pause/{id}', [TimerController::class, 'pause']);
    
    // Leave Management
    Route::post('add/leave', [LeaveManagementController::class, 'create']);
    Route::get('edit/leave/{id}', [LeaveManagementController::class, 'edit']);
    Route::get('get/leaves', [LeaveManagementController::class, 'index']);
    Route::post('update/leave', [LeaveManagementController::class, 'update']);
    Route::get('get/status/leave/management', [LeaveManagementController::class, 'get_status']);

    // Policies
    Route::post('add/policies', [PoliciesManagementController::class, 'store']);
});

