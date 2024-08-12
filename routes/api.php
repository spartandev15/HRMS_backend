<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PoliciesManagementController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\TimerController;
use App\Http\Controllers\Api\JobDetailController;
use App\Http\Controllers\Api\EmployeeProfile;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\LeaveManagementController;
use App\Http\Controllers\Api\EmployeeProfileController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;

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
    Route::post("employee/login","login")->name('login');
    Route::get("token-check","checkToken")->name('token-check')->middleware('isBanned');
});



Route::middleware('auth:api')->group(function () {
    // Protected route to get the authenticated user
    Route::get('/employee', function (Request $request) {
        Route::post('update/profile', [AuthController::class, 'employeeupdate_profile']);
  
    });
   
    // Protected route for logout
    Route::post('/update/profile', [AuthController::class, 'update_profile']);
    Route::get('get/profile', [AuthController::class, 'get_profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('upload-documents', [AuthController::class, 'upload_document']);
    Route::post('upload-profile-image', [AuthController::class, 'upload_profile_image']);
   
    // User Address
    Route::post('user/address', [AuthController::class, 'address']);
    // Emergency Contact Data
    Route::post('user/emergency-contact', [AuthController::class, 'emergency_contact']);


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
    Route::post('projects/timers/get', [TimerController::class, 'get']);
    Route::get('projects/timers/punchin', [TimerController::class, 'punch_in']);
    Route::get('projects/timers/punchout', [TimerController::class, 'punch_out']);
    Route::post('projects/timers/screenshot', [TimerController::class, 'take_screeshot']);
    Route::get('projects/timers/get/detail', [TimerController::class,'get_detail']);
 
    // Leave  Management
    Route::post('add/leave', [LeaveManagementController::class, 'create']);
    Route::get('edit/leave/{id}', [LeaveManagementController::class, 'edit']);
    Route::get('get/leaves', [LeaveManagementController::class, 'index']);
    Route::post('update/leave', [LeaveManagementController::class, 'update']);
    Route::get('get/status/leave/management', [LeaveManagementController::class, 'get_status']);

    // Policies
    Route::post('add/policies', [PoliciesManagementController::class, 'store']);

     // holiday 
     Route::post('add/events', [EventsController::class, 'create']);
     Route::get('edit/events/{id}', [EventsController::class, 'edit']);
     Route::get('get/events', [EventsController::class, 'index']);
     Route::post('update/events', [EventsController::class, 'update']);
      // Dashboard 
     Route::get('dashboard', [DashboardController::class, 'index']);
 
     // Job Details
     Route::post('update/job-details', [JobDetailController::class, 'job_store']);
     Route::post('update/eduction/details', [JobDetailController::class,'education_details']);
     Route::post('update/work-experience', [JobDetailController::class,'work_experience']);
     Route::post('update/salary-detail', [JobDetailController::class,'salary_detail']);
    
    // Employee Profile data 
    Route::post('add/employe/profile', [EmployeeProfileController::class, 'create']);
    Route::get('edit/employe/profile/{id}', [EmployeeProfileController::class, 'edit']);
    Route::get('get/employe/profile', [EmployeeProfileController::class, 'index']);
    Route::post('update/employe/profile', [EmployeeProfileController::class, 'update']);

    // EMPLOYEE  CREATE DATA 
    Route::post('create/employee', [EmployeeController::class, 'create']);
    Route::post('update/employee', [EmployeeController::class, 'update']);
    Route::get('get/employee', [EmployeeController::class, 'get_employee']);
    Route::get('get/all/employee', [EmployeeController::class, 'get_allemployee']);
    Route::post('delete/employee', [EmployeeController::class, 'delete_employee']);
});  
      
