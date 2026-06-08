<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\JobDetailController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TimerController;
use App\Http\Controllers\Api\NoticeBoardController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\LeaveManagementController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\UserDocumentController;
use App\Http\Controllers\Api\EmployeeTrainingInternshipController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

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
    Route::post('signup', [AuthController::class, 'register']);
    Route::post('register/company', [AuthController::class, 'thirdpartyDatastoreAPI']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post("employee/login","login")->name('login');
    // GET all products
    Route::get('products/lists', [ProductController::class, 'index']);

    // POST /api/products 
    // Create a new product
    Route::post('products/store', [ProductController::class, 'store']);
   
    // GET /api/products/{id}
    // Get a product by ID
    Route::get('products/edit/{id}', [ProductController::class, 'show']);

    // PUT /api/products/{id}
    // Update a product by ID
    Route::put('products/update/{id}', [ProductController::class, 'update']);

    // DELETE /api/products/{id}
    // Delete a product by ID
    Route::delete('products/destroy/{id}', [ProductController::class, 'destroy']);
        // Route::post("register","register");
});

Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::post('orpect-sync-employee', [EmployeeController::class, 'OrpectSYncEmployee']);
Route::post('orpect-sync-employee-update', [EmployeeController::class, 'OrpectSYncEmployeeUpdate']);
Route::post('orpect-sync-employee-delete', [EmployeeController::class, 'OrpectSYncEmployeeDelete']);
Route::middleware('auth:api')->group(function () {
    // Protected route to get the authenticated user
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
    Route::get('/employee', function (Request $request) {   
        Route::post('update/profile', [AuthController::class, 'employeeupdate_profile']);
    });  
    // Protected route for logout
    Route::post('/update/profile', [AuthController::class, 'update_profile']);
    Route::post('/update/profile-image', [AuthController::class, 'upload_profile_image']);
    Route::get('get/profile', [AuthController::class, 'get_profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

  
    // User Address
    Route::post('user/address', [AuthController::class, 'address_data']);
    // Emergency Contact Data
    Route::post('user/emergency_contact', [AuthController::class, 'emergency_contact_update']);
        
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
    Route::post('projects/timers/get', [TimerController::class, 'get']);  // provide all the data from timers table no filter
    Route::post('projects/timers/punchin', [TimerController::class, 'punch_in']); // add punch in details of logged in user and provided both users details along with current puched it details
    Route::post('projects/timers/punchout', [TimerController::class, 'punch_out']); // puched out current logged out user and provide his details
    Route::post('projects/timers/screenshot', [TimerController::class, 'take_screeshot']);
    Route::get('projects/timers/get/detail', [TimerController::class,'get_detail']); // provide timer details for logged in user for current date only
    Route::post('projects/timers/resumetime', [TimerController::class, 'resumetime']);
    
    // Job Details
    Route::post('update/job-details', [JobDetailController::class, 'job_store']);
    Route::post('update/eduction/details', [JobDetailController::class,'education_details']);
    Route::post('update/work-experience', [JobDetailController::class,'work_experience']);
    Route::post('update/salary-detail', [JobDetailController::class,'salary_detail']);
    
     // notice board  data 
     Route::post('add/notice-board', [NoticeBoardController::class, 'create']);
     Route::get('edit/notice-board/{id}', [NoticeBoardController::class, 'edit']);
     Route::get('get/notice-board', [NoticeBoardController::class, 'index']);
     Route::post('update/notice-board', [NoticeBoardController::class, 'update']);
 

      // Dashboard 
      Route::get('dashboard', [DashboardController::class, 'index']);
      Route::get('hr/dashboard', [DashboardController::class, 'hr_dashboard']);
    
      // EMPLOYEE  CREATE DATA 
      Route::post('create/employee', [EmployeeController::class, 'create']);
      Route::get('get/all/employee', [EmployeeController::class, 'get_allemployee']);
      Route::get('export/employee', [EmployeeController::class, 'export_employee']);
      Route::post('update/employee', [EmployeeController::class, 'update']);
      Route::get('get/employee', [EmployeeController::class, 'get_employee']);
      Route::post('delete/employee', [EmployeeController::class, 'delete_employee']);
      Route::get('employee/birthday/wishes', [EmployeeController::class, 'employee_birthday_wish']);
      Route::get('employee/aniversary/wishes', [EmployeeController::class, 'employee_birthday_wish']);
      Route::get('get/employee/{id}', [EmployeeController::class, 'getEmployeeByID']);
      Route::get('get/birthdays', [EmployeeController::class, 'get_birthdays']);
      Route::get('get/anniversaries', [EmployeeController::class, 'get_anniversaries']);
      Route::get('search_employee_by_id', [EmployeeController::class, 'search_employee_by_id']);



      
    // Leave  Management 
    Route::post('add/leave', [LeaveManagementController::class, 'create']);
    Route::get('edit/leave/{id}', [LeaveManagementController::class, 'edit']);
    Route::get('get/leaves', [LeaveManagementController::class, 'index']);  //filtered by date
    Route::get('get/all/leaves', [LeaveManagementController::class, 'all_leaves']);   // normally getting all leaves
    Route::post('update/leave', [LeaveManagementController::class, 'update']);
    Route::get('get/status/leave/management', [LeaveManagementController::class, 'get_status']);
    Route::post('change/leave/status', [LeaveManagementController::class, 'change_leave_status']);
    Route::get('get/employeesLeaves', [LeaveManagementController::class, 'getEmployeesLeaves']);
    Route::get('get/userLeaves/{id}', [LeaveManagementController::class, 'getUserLeaves']);
    Route::get('get/employeeLeaves/{id}', [LeaveManagementController::class, 'getEmployeeLeaves']);  //get all applied leaves of an employee by id


    // Events 
    Route::post('add/events', [EventsController::class, 'create']);
    Route::get('edit/events/{id}', [EventsController::class, 'edit']);
    Route::get('get/events', [EventsController::class, 'index']);
    Route::post('update/events', [EventsController::class, 'update']);
    Route::post('delete/events', [EventsController::class, 'destroy']);

     // Payroll Process data
     Route::post('add/payroll', [PayrollController::class, 'create']);
     Route::get('edit/payroll/{id}', [PayrollController::class, 'edit']);
     Route::get('get/payroll', [PayrollController::class, 'index']);
     Route::post('update/payroll', [PayrollController::class, 'update']);

     Route::post('create/category', [CategoryController::class, 'create']);
     Route::get('get/all/category', [CategoryController::class, 'get_allcategory']);
     Route::post('update/category', [CategoryController::class, 'update']);
     Route::post('delete/category', [CategoryController::class, 'delete_category']);

     //overtime api 
     Route::post('add/overtime', [EmployeeController::class, 'storeOvertime']);
     Route::get('get/overtime', [EmployeeController::class, 'getOvertimeRecords']);
     Route::post('update/overtime', [EmployeeController::class, 'updateOvertimeRecord']);
     Route::post('update/overtimeRecordStatus', [EmployeeController::class, 'overtimeRecordStatus']);

     Route::get('get/employees/overtimeRecords', [EmployeeController::class, 'getEmployeesOvertimeRecords']);

     Route::post('create/Notice', [EmployeeController::class, 'createNotice']);
     Route::get('employees/emails', [EmployeeController::class, 'getEmails']);
     // Define route for fetching notices for the currently logged-in user
    Route::get('get/notices', [EmployeeController::class, 'getNotices']);
    Route::get('get/allnotices', [EmployeeController::class, 'getAllNotices']);

    Route::post('create/interview', [InterviewController::class, 'storeInterview']);
    Route::get('get/interviews', [InterviewController::class, 'getAllInterviews']);
    // Route::get('get/interviewByID', [InterviewController::class, 'getInterviewById']);
    Route::post('update/interview', [InterviewController::class, 'updateInterview']);
    Route::post('delete/interview', [InterviewController::class, 'deleteInterview']);
    
    Route::post('create/vacancy', [InterviewController::class, 'storeVacancy']);
    Route::get('get/vacancies', [InterviewController::class, 'getAllVacancies']);
    Route::get('get/getVacancyById', [InterviewController::class, 'getVacancyById']);
    Route::post('update/vacancy', [InterviewController::class, 'updateVacancy']);
    Route::post('delete/vacancy', [InterviewController::class, 'deleteVacancy']);
    
    Route::post('upload/documents', [UserDocumentController::class, 'uploadDocuments']);
    Route::get('get/getDocumentById', [UserDocumentController::class, 'getDocumentById']);
    Route::get('get/getDocumentDetailsById/{id}', [UserDocumentController::class, 'getDocumentDetailsById']);
    Route::get('get/documents', [UserDocumentController::class, 'getAllDocuments']);
    Route::post('update/documentRecordStatus', [UserDocumentController::class, 'updateDocumentStatus']);

    Route::post('employee/training/store', [EmployeeTrainingInternshipController::class, 'store']);
    Route::get('employee/training/list', [EmployeeTrainingInternshipController::class, 'index']);
    Route::get('employee/training/edit/{id}', [EmployeeTrainingInternshipController::class, 'show']);
    Route::delete('employee/training/delete/{id}', [EmployeeTrainingInternshipController::class, 'destroy']);
    Route::post('employee/training/update/{id}', [EmployeeTrainingInternshipController::class, 'update']);
  
    Route::get('/clear-all-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');
        return response()->json(['message' => 'All caches cleared successfully.']);
    });

});
   




