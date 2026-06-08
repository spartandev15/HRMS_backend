<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use App\Models\Leave;
use App\Models\Notice;
use App\Models\LeaveManagement;
use App\Models\JobDetail;
use App\Models\User;
use App\Models\Salary;
use App\Models\User_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Part\HtmlPart;


class EmployeeController extends Controller
{

    public function create(Request $request)
    {
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $request->email)->first() != null) {
                return $this->registrationFailed('Email already exists.');
            }
        }
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'email' => 'required|email|unique:users,email', // Ensure email is unique in users table
            'employee_id' => 'required|unique:users,employee_id', // Ensure employee_id is unique in users table
            'basic_salary' => 'required|numeric|min:0',
            'house_rent' => 'required|numeric|min:0',
            'medical_allowance' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'leave_deduction' => 'required|numeric|min:0',
            'pf' => 'required|numeric|min:0',
            'employee_state' => 'required|string|max:255',
            'insurance' => 'required|numeric|min:0',
            'extra_working' => 'required|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'bank_name' => 'required|string|max:255',
            'bank_ifsc' => 'required|string|max:20',
            'account_number' => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:255',
            'profile_photo' => 'nullable|max:2048', 
        ]);
    
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }    
        $data = $request->all();
        
        $user = $this->store($data);
        if ($user) {

            return response()->json([
                'result' => true,
                'message' => 'Employee created successfully.',
                'user' => $user,
                // 'dataemployeee'=>$dataemployeee,
            ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }
    public function OrpectSYncEmployeeUpdate(Request $request){
        $data = $request->all();
        $user = $this->syncUpdateData($data);
        if ($user) {
            return response()->json([
                'result' => true,
                'message' => 'Employee created successfully.',
                'user' => $user
            ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }
   protected function syncUpdateData(Request $request)
{
    $profilePhotoPath = '';

    $employee = Employee::where([
        'orpect_employee_id' => $request->orpect_employee_id,
        // 'orpect_user_id' => $request->orpect_user_id,
    ])->update([

        'user_id' => $request->id,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'date_of_birth' => $request->date_of_birth ?? '',
        'designation' => $request->designation,
        'employee_id' => $request->employee_id,
        'email' => $request->email,
        'joining_date' => $request->joining_date,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'profile_photo' => $profilePhotoPath ?? '',

    ]);

    return $employee;
}
    public function OrpectSYncEmployee(Request $request)
    {
          
        $data = $request->all();
        $user = $this->syncstoreData($data);
        if ($user) {
            return response()->json([
                'result' => true,
                'message' => 'Employee created successfully.',
                'user' => $user
            ]);
        } else {                                                                                                                                                                
            return $this->registrationFailed("Registration failed");
        }
    }
    protected function syncstoreData(array $data)
    {
        // if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $user = User::updateOrCreate(
        [
            'email' => $data['email'] // Check by email
        ],
        [
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'status'         => $data['role'],
            'password'       => !empty($data['password']) ?  Hash::make($data['password']) : '',
            'designation'    => !empty($data['designation']) ? $data['designation'] : '',
            'employee_id'    => $data['employee_id'],
            'orpect_user_id' => $data['orpect_user_id'],
        ]
         );
    
            // Handle profile photo upload (if provided)
            $profilePhotoPath = null;
           
        $userDetail = User_Detail::updateOrCreate(
        [
            'user_id' => $user->id
        ],
        [
                'phone' => $data['phone'],
                'user_id' => $user->id,
                'joining_date' => $data['joining_date'],
                'profile_photo' => $profilePhotoPath, // Store the full URL of the profile photo
            ]);
        $employee = Employee::updateOrCreate(
        [
            'user_id' => $user->id
        ],
        [
                'user_id' => $user->id,
                'first_name' => $data['first_name'] ,
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'] ?? '',
                'designation' => $data['designation'],
                'employee_id' => $data['employee_id'],
                'email' => $data['email'],
                'joining_date' => $data['joining_date'],
                'phone' => $data['phone'],
                'orpect_employee_id' => $data['orpect_employee_id'],
                'orpect_user_id' => $data['orpect_user_id'],
                'password' => Hash::make($data['password']),
                'profile_photo' => $profilePhotoPath ? $profilePhotoPath : '', // Store the full URL of the profile photo
            ]);
            $leaves = [
                'sick_leaves' => [
                    'Taken' => 0,
                    'Total' => $data['sick_leaves'],
                    'Pending' => $data['sick_leaves']
                ],
                'paid_leaves' => [
                    'Taken' => 0,
                    'Total' => $data['paid_leaves'],
                    'Pending' => $data['paid_leaves']
                ],
                'unpaid_leaves' => [
                    'Taken' => 0,
                    'Total' => $data['unpaid_leaves'],
                    'Pending' => $data['unpaid_leaves']
                ]
            ];
            $json_data = json_encode($leaves);
            $leaveData = Leave::create([
                'user_id' => $user->id,
                'emp_name' => $data['first_name'] . ' ' . $data['last_name'],
                'leave_data' => $json_data,
                'overall_total_leaves' => $data['total_leaves'],
                'taken' => 0,
                'pending' => $data['total_leaves'] ?? '',
            ]);    
            // $leaveData->leave_data = json_decode($leaveData->leave_data, true); // Decode as an associative array
            $salaryData = Salary::create([
                'user_id' => $user->id,
                'employee_name' => $data['first_name'] . ' ' . $data['last_name'],
                'employee_id' => $data['employee_id'],
                'basic_salary' => $data['basic_salary'] ?? 0,
                'house_rent' => $data['house_rent']  ?? 0,
                'medical_allowance' => $data['medical_allowance'] ?? '',
                'tax' => $data['tax'] ?? 0,
                'leave_deduction' => $data['leave_deduction'] ?? 0,
                'pf' => $data['pf'] ?? 0,
                'employee_state' => $data['employee_state'] ?? 0,
                'insurance' => $data['insurance'] ?? 0,
                'extra_working' => $data['extra_working'] ?? 0,
                'gross_total' => $data['gross_salary'] ?? 0,
                'final_total' => $data['gross_salary'] ?? 0 - $data['tax'] ?? 0 - $data['leave_deduction'] ?? 0,
                'gross_salary' => $data['gross_salary'] ?? 0,
                'bank_name' => $data['bank_name'] ?? '',
                'bank_ifsc' => $data['bank_ifsc'] ?? '',
                'account_number' => $data['account_number'] ?? '',
                'account_holder_name' => $data['account_holder_name'] ?? '',
            ]);
            $finalData = [
                'User Details' => $user,
                'Employee' => $employee,  // Now returning employee data as well
                'Leaves Data' => $leaveData,
                'Salary Data' => $salaryData,
            ];    
            return $finalData;
        // }
        // return null;
    }
    protected function store(array $data)
    {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'status' => $data['role'],
                'password' => Hash::make($data['password']),
               'designation' =>  $data['role'],
                'employee_id' => $data['employee_id'],
            ]);
    
            // Handle profile photo upload (if provided)
            $profilePhotoPath = null;
            if (isset($data['profile_photo']) && !empty($data['profile_photo'])) {
                $file = $data['profile_photo']; // or $request->file('profile_photo') if you are passing the file from the request   
                if ($file && $file->isValid()) {
                    $folderPath = public_path('profile-photo/' . $user->id);    
                    if (!File::exists($folderPath)) {
                        File::makeDirectory($folderPath, 0755, true);
                    }
                    $filename = $file->getClientOriginalName();   
                    $timestamp = now()->format('YmdHis');
                    $uniqueFilename = $timestamp . '_' . $filename;
                    $relativeFilePath = 'profile-photo/' . $user->id . '/' . $uniqueFilename;
                    $fileUrl = asset('public/profile-photo/' . $user->id . '/' . $uniqueFilename);
                    $file->move($folderPath, $uniqueFilename);
                    $profilePhotoPath = $fileUrl;
                }
            }
            $userDetail = User_Detail::create([
                'phone' => $data['phone'],
                'user_id' => $user->id,
                'joining_date' => $data['joining_date'],
                'profile_photo' => $profilePhotoPath, // Store the full URL of the profile photo
            ]);
            $employee = Employee::create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'] ?? '',
                'designation' => $data['role'],
                'employee_id' => $data['employee_id'],
                'email' => $data['email'],
                'joining_date' => $data['joining_date'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'profile_photo' => $profilePhotoPath ? $profilePhotoPath : '', // Store the full URL of the profile photo
            ]);
          
            $leaves = [
                'sick_leaves' => [
                    'Taken' => 0,
                    'Total' => $data['sick_leaves'],
                    'Pending' => $data['sick_leaves']
                ],
                'paid_leaves' => [
                    'Taken' => 0,
                    'Total' => $data['paid_leaves'],
                    'Pending' => $data['paid_leaves']
                ],
                'unpaid_leaves' => [
                    'Taken' => 0,
                    'Total' => $data['unpaid_leaves'],
                    'Pending' => $data['unpaid_leaves']
                ]
            ];
            $json_data = json_encode($leaves);
            $leaveData = Leave::create([
                'user_id' => $user->id,
                'emp_name' => $data['first_name'] . ' ' . $data['last_name'],
                'leave_data' => $json_data,
                'overall_total_leaves' => $data['total_leaves'],
                'taken' => 0,
                'pending' => $data['total_leaves'],
            ]);    
            $leaveData->leave_data = json_decode($leaveData->leave_data, true); // Decode as an associative array
            $salaryData = Salary::create([
                'user_id' => $user->id,
                'employee_name' => $data['first_name'] . ' ' . $data['last_name'],
                'employee_id' => $data['employee_id'],
                'basic_salary' => $data['basic_salary'],
                'house_rent' => $data['house_rent'],
                'medical_allowance' => $data['medical_allowance'],
                'tax' => $data['tax'],
                'leave_deduction' => $data['leave_deduction'],
                'pf' => $data['pf'],
                'employee_state' => $data['employee_state'],
                'insurance' => $data['insurance'],
                'extra_working' => $data['extra_working'],
                'gross_total' => $data['gross_salary'],
                'final_total' => $data['gross_salary'] - $data['tax'] - $data['leave_deduction'],
                'gross_salary' => $data['gross_salary'],
                'bank_name' => $data['bank_name'],
                'bank_ifsc' => $data['bank_ifsc'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'],
            ]);
            $data['added_by']=$user->id;
            // dd($data);
            $dataemployeee =   $this->syncEmployeeToOrpect($data);
            dd($dataemployeee);
            $finalData = [
                'User Details' => $user,
                'Employee' => $employee,  // Now returning employee data as well
                'Leaves Data' => $leaveData,
                'Salary Data' => $salaryData,
               'Sync Employee' =>$dataemployeee,
            ];    
            return $finalData;
        }
        return null;
    }
    
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required', // Ensure employee ID is provided
            'profile_photo' => 'nullable|max:2048',  // Validate profile photo (optional)
        ]);

        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }

        $user = $this->updated_data($request->all());
        if ($user) {
            $leaveData = json_decode($user->leave->leave_data);    
            return response()->json([
                'result' => true,
                'message' => 'Employee Data Updated',
                'user' => $user,
                'leave' => $leaveData,  // Include parsed leave data
                'access_token' => '',
                'token_type' => ''
            ]);
        } else {
            return $this->registrationFailed("Update failed.");
        }
    }
    
    protected function updated_data(array $data)
    {
        // Find the employee data based on the ID
        $employee_data = Employee::where('id', $data['id'])->first();
    
        // If no employee found, return null
        if (!$employee_data) {
            return null;
        }
    
        // Prepare the update data for User model (users table)
        $user_update_data = [];
    
        // Check if fields are present in the request and add them to the update array (for User model)
        if (!empty($data['first_name'])) $user_update_data['first_name'] = $data['first_name'];
        if (!empty($data['last_name'])) $user_update_data['last_name'] = $data['last_name'];
        if (!empty($data['email'])) $user_update_data['email'] = $data['email'];  // Email is common for both
        if (!empty($data['status'])) $user_update_data['status'] = 'employee';  // Assuming status should always be 'employee' for employees
        if (!empty($data['designation'])) $user_update_data['designation'] = $data['designation'];
        if (!empty($data['employee_id'])) $user_update_data['employee_id'] = $data['employee_id'];
        if (!empty($data['line_manager'])) $user_update_data['line_manager'] = $data['line_manager'];  // Line manager could be optional
        if (!empty($data['joining_date'])) $user_update_data['joining_date'] = $data['joining_date'];  // Joining date is common
    
        // Handle profile photo upload if present
        $profilePhotoUrl = null;
        if (isset($data['profile_photo']) && $data['profile_photo'] instanceof \Illuminate\Http\UploadedFile) {
            $profilePhotoUrl = $this->uploadProfilePhoto($data['profile_photo'], $employee_data->user_id);
            if ($profilePhotoUrl) {
                // Update the profile photo only if a new one is uploaded for the User model
              //  $user_update_data['profile_photo'] = $profilePhotoUrl;
            }
        }
    
        // Update the User model if there are any changes
        if (!empty($user_update_data)) {
            User::where('id', $employee_data->user_id)->update($user_update_data);
        }
    
        // Prepare the update data for Employee model (employees table)
        $employee_update_data = [];
    
        // Check if fields are present in the request and add them to the update array (for Employee model)
        if (!empty($data['first_name'])) $employee_update_data['first_name'] = $data['first_name'];
        if (!empty($data['last_name'])) $employee_update_data['last_name'] = $data['last_name'];
        if (!empty($data['email'])) $employee_update_data['email'] = $data['email']; // Email is common for both
        if (!empty($data['date_of_birth'])) $employee_update_data['date_of_birth'] = $data['date_of_birth'];  // Date of birth is common for both
        if (!empty($data['designation'])) $employee_update_data['designation'] = $data['designation'];
        if (!empty($data['employee_id'])) $employee_update_data['employee_id'] = $data['employee_id'];
        if (!empty($data['joining_date'])) $employee_update_data['joining_date'] = $data['joining_date'];  // Joining date is common
        if (!empty($data['phone'])) $employee_update_data['phone'] = $data['phone'];
    
        // If the profile photo URL is updated, include it in the employee update data as well
        if ($profilePhotoUrl) {
            $employee_update_data['profile_photo'] = $profilePhotoUrl;
        }
    
        // Update the Employee model if there are any changes
        if (!empty($employee_update_data)) {
            Employee::where('id', $data['id'])->update($employee_update_data);
        }
    
        $leave_update_data = [];

        // Fetch existing leave data from the database
        $existing_leave = Leave::where('user_id', $employee_data->user_id)->first();
        
        if ($existing_leave) {
            // Decode the leave_data JSON object from the existing record
            $leave_data = json_decode($existing_leave->leave_data, true);
        } else {
            // If no existing leave data is found, initialize with default values
            $leave_data = [
                'paid_leaves' => [
                    'Taken' => 0,
                    'Total' => 6,
                    'Pending' => 6
                ],
                'sick_leaves' => [
                    'Taken' => 0,
                    'Total' => 6,
                    'Pending' => 6
                ],
                'unpaid_leaves' => [
                    'Taken' => 0,
                    'Total' => 6,
                    'Pending' => 6
                ]
            ];
        }
        
        // Update Total values based on the request
        if (isset($data['paid_leaves'])) {
            $leave_data['paid_leaves']['Total'] = $data['paid_leaves'];
        }
        if (isset($data['sick_leaves'])) {
            $leave_data['sick_leaves']['Total'] = $data['sick_leaves'];
        }
        if (isset($data['unpaid_leaves'])) {
            $leave_data['unpaid_leaves']['Total'] = $data['unpaid_leaves'];
        }
        
        // Adjust Taken and Pending based on existing records
        // Example: If user has taken 3 leaves already, set Taken to 3 and Pending to Total - Taken
        if (isset($data['paid_leaves_taken'])) {
            $leave_data['paid_leaves']['Taken'] = $data['paid_leaves_taken'];
        } else {
            $leave_data['paid_leaves']['Pending'] = $leave_data['paid_leaves']['Total'] - $leave_data['paid_leaves']['Taken'];
        }
        
        if (isset($data['sick_leaves_taken'])) {
            $leave_data['sick_leaves']['Taken'] = $data['sick_leaves_taken'];
        } else {
            $leave_data['sick_leaves']['Pending'] = $leave_data['sick_leaves']['Total'] - $leave_data['sick_leaves']['Taken'];
        }
        
        if (isset($data['unpaid_leaves_taken'])) {
            $leave_data['unpaid_leaves']['Taken'] = $data['unpaid_leaves_taken'];
        } else {
            $leave_data['unpaid_leaves']['Pending'] = $leave_data['unpaid_leaves']['Total'] - $leave_data['unpaid_leaves']['Taken'];
        }
        
        // Recalculate overall totals
        $overall_total_leaves = $leave_data['paid_leaves']['Total'] + $leave_data['sick_leaves']['Total'] + $leave_data['unpaid_leaves']['Total'];
        $overall_taken = $leave_data['paid_leaves']['Taken'] + $leave_data['sick_leaves']['Taken'] + $leave_data['unpaid_leaves']['Taken'];
        $overall_pending = $overall_total_leaves - $overall_taken;
        
        // Update leave_update_data with recalculated overall totals
        $leave_update_data['leave_data'] = json_encode($leave_data);
        $leave_update_data['overall_total_leaves'] = $overall_total_leaves;
        $leave_update_data['taken'] = $overall_taken;
        $leave_update_data['pending'] = $overall_pending;
        
        // Update the Leave model with the modified leave data
        if (!empty($leave_update_data)) {
            Leave::where('user_id', $employee_data->user_id)->update($leave_update_data);
        }
        

    
        // Prepare the update data for Salary model
        $salary_update_data = [];
    
        // Ensure employee name is updated in Salary table
        // if (!empty($data['first_name'])) $salary_update_data['first_name'] = $data['first_name'];
        // if (!empty($data['last_name'])) $salary_update_data['last_name'] = $data['last_name'];
        if (!empty($data['first_name']) || !empty($data['last_name'])) $salary_update_data['employee_name'] = trim($data['first_name'] . ' ' . $data['last_name']);

        if (!empty($data['basic_salary'])) $salary_update_data['basic_salary'] = $data['basic_salary'];
        if (!empty($data['house_rent'])) $salary_update_data['house_rent'] = $data['house_rent'];
        if (!empty($data['medical_allowance'])) $salary_update_data['medical_allowance'] = $data['medical_allowance'];
        if (!empty($data['tax'])) $salary_update_data['tax'] = $data['tax'];
        if (!empty($data['leave_deduction'])) $salary_update_data['leave_deduction'] = $data['leave_deduction'];
        if (!empty($data['pf'])) $salary_update_data['pf'] = $data['pf'];
        if (!empty($data['employee_state'])) $salary_update_data['employee_state'] = $data['employee_state'];
        if (!empty($data['insurance'])) $salary_update_data['insurance'] = $data['insurance'];
        if (!empty($data['extra_working'])) $salary_update_data['extra_working'] = $data['extra_working'];
        if (!empty($data['gross_total'])) $salary_update_data['gross_total'] = $data['gross_total'];
        if (!empty($data['final_total'])) $salary_update_data['final_total'] = $data['final_total'];
        if (!empty($data['gross_salary'])) $salary_update_data['gross_salary'] = $data['gross_salary'];
        if (!empty($data['bank_name'])) $salary_update_data['bank_name'] = $data['bank_name'];
        if (!empty($data['bank_ifsc'])) $salary_update_data['bank_ifsc'] = $data['bank_ifsc'];
        if (!empty($data['account_number'])) $salary_update_data['account_number'] = $data['account_number'];
        if (!empty($data['account_holder_name'])) $salary_update_data['account_holder_name'] = $data['account_holder_name'];
    
        // Update the Salary model if there are any changes
        if (!empty($salary_update_data)) {
            Salary::where('user_id', $employee_data->user_id)->update($salary_update_data);
        }
    
        // Prepare the update data for User_Detail model
        $user_detail_update_data = [];
    
        if (!empty($data['phone'])) $user_detail_update_data['phone'] = $data['phone'];
        if (!empty($data['joining_date'])) $user_detail_update_data['joining_date'] = $data['joining_date'];
        if (!empty($data['emp_id'])) $user_detail_update_data['emp_id'] = $data['emp_id'];
        if ($profilePhotoUrl) {
            $user_detail_update_data['profile_photo'] = $profilePhotoUrl;
        }
        if (!empty($data['country'])) $user_detail_update_data['country'] = $data['country'];
        if (!empty($data['city'])) $user_detail_update_data['city'] = $data['city'];
        if (!empty($data['state'])) $user_detail_update_data['state'] = $data['state'];
        if (!empty($data['zipcode'])) $user_detail_update_data['zipcode'] = $data['zipcode'];
        if (!empty($data['emergency_name'])) $user_detail_update_data['emergency_name'] = $data['emergency_name'];
        if (!empty($data['relationship'])) $user_detail_update_data['relationship'] = $data['relationship'];
        if (!empty($data['emergency_phone'])) $user_detail_update_data['emergency_phone'] = $data['emergency_phone'];
        if (!empty($data['emergency_mobileno'])) $user_detail_update_data['emergency_mobileno'] = $data['emergency_mobileno'];
    
        // Update the User_Detail model
        if (!empty($user_detail_update_data)) {
            User_Detail::where('user_id', $employee_data->user_id)->update($user_detail_update_data);
        }
    
        // Fetch all updated details of the user
        $user = User::with(['userDetail', 'employee', 'salary', 'leave'])->where('id', $employee_data->user_id)->first();
    
        // Return the updated user details as an array of objects
        return $user;
    }

    protected function uploadProfilePhoto($file, $userId)
    {
        // Define the folder path
        $folderPath = public_path('profile-photo/' . $userId);
    
        // Create the directory if it doesn't exist
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }
    
        // Generate a unique filename
        $filename = $file->getClientOriginalName();
        $timestamp = now()->format('YmdHis');
        $uniqueFilename = $timestamp . '_' . $filename;
    
        // Move the file to the folder
        $file->move($folderPath, $uniqueFilename);
    
        // Return the URL of the profile photo
        return 'https://hrmsdb.spartanstaging.site/public/profile-photo/' . $userId . '/' . $uniqueFilename;
    }
    
    protected function loginFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
            'access_token' => '',
            'token_type' => '',
            "user"=>   [
                'name' => "",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
                ]
        ]);
    }

    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
            'access_token' => '',
            'token_type' => ''
        ]);
    }

    public function get_employee(Request $request){
        $employe_id = $request->id;
        $employe_data =  Employee::where('id',$employe_id)->first();
       
        return response()->json([   
            'result' => true,
            'message' => 'Get Employee Data',
            "employee"=>$employe_data
        ]);
    }

    public function delete_employee(Request $request)
    {
        $id = $request->id;
        $user = User::where('id', $id)->first();
        if (!is_null($user)) {
            // Delete related records
            User::where('id', $user->id)->delete();
            User_Detail::where('user_id', $user->id)->delete();
            JobDetail::where('user_id', $user->id)->delete();
            LeaveManagement::where('user_id', $user->id)->delete();
            Leave::where('user_id', $user->id)->delete();
            Employee::where('user_id', $user->id)->delete();
            
            // Delete the employee's leave record
            if ($user->leaves) {
                $user->leaves->delete();  // Delete the related leave record
            }
            
            // Delete the employee
            $user->delete();
            
            return response()->json([   
                'result' => true,
                'message' => 'Employee and all related data are deleted',
            ]);
        } else {
            return response()->json([   
                'result' => false,
                'message' => 'Employee ID data is not valid',
            ]);
        }
    }
    public function OrpectSYncEmployeeDelete(Request $request)
    {
        $id = $request->orpect_user_id;
        $id = $request->orpect_employee_id;
        $user = User::where('orpect_user_id', $id)->first();
        if (!is_null($user)) {  
            // Delete related records
            User::where('id', $user->id)->delete();
            User_Detail::where('user_id', $user->id)->delete();
            JobDetail::where('user_id', $user->id)->delete();
            LeaveManagement::where('user_id', $user->id)->delete();
            Leave::where('user_id', $user->id)->delete();
            Employee::where('user_id', $user->id)->delete();
            
            // Delete the employee's leave record
            if ($user->leaves) {
                $user->leaves->delete();  // Delete the related leave record
            }
            
            // Delete the employee
            $user->delete();
            
            return response()->json([   
                'result' => true,
                'message' => 'Employee and all related data are deleted',
            ]);
        } else {
            return response()->json([   
                'result' => false,
                'message' => 'Employee ID data is not valid',
            ]);
        }
    }
    public function employee_birthday_wish(Request $request){
        $today = Carbon::today()->format('m-d'); // Format: month-day
        // dump($today);
    
        // Get users whose date of birth is today or in the future, and order by dob ascending
        $user_details = User_Detail::whereRaw('DATE_FORMAT(dob, "%m-%d") >= ?', [$today])
                                   ->orderByRaw('DATE_FORMAT(dob, "%m-%d") ASC')
                                   ->with('user_info')
                                   ->get();
        
        return response()->json([   
            'result' => true,
            'message' => 'Employee id data is not valid',
            'user' => $user_details,
        ]);
    }
    
    public function employee_anniversary_wish(Request $request){
        $today = Carbon::today()->format('m-d'); // Format: month-day
         // Get users whose date of birth is today or in the future, and order by dob ascending
        $user_details = User_Detail::whereRaw('DATE_FORMAT(joining_date, "%m-%d") >= ?', [$today])
                                   ->orderByRaw('DATE_FORMAT(joining_date, "%m-%d") ASC')
                                   ->with('user_info')
                                   ->get();
        
        return response()->json([   
            'result' => true,
            'message' => 'Employee id data is not valid',
            'user' => $user_details,
        ]);
    }


    public function getEmployeeByID($id)
    {
        // Fetch the employee along with the related user detail, leaves, and salary data
        $employee = Employee::where('user_id', $id)
        ->with(['userDetail', 'leaves', 'salary', 'user'])  // Load 'user' relationship as well
        ->first();
        // echo "testing";die;
        // echo "<pre>";print_r($employee);die;
        if (!$employee) {
            return response()->json((object)[
                'result' => false,
                'message' => 'Employee not found',
            ]);
        }
    
        // Decode the leave_data JSON string into an object
        if ($employee->leaves && $employee->leaves->leave_data) {
            $employee->leaves->leave_data = json_decode($employee->leaves->leave_data);
        }
    
        // Prepare the response data
        $responseData = [
            'result' => true,
            'message' => 'User data retrieved successfully!',
            'user' => [
                'user_details' => [
                    'user_id' => $employee->user_id,
                    'id' => $employee->id,  // Access 'name' from the 'user' relationship
                    'first_name' => $employee->user->first_name,  // Access 'name' from the 'user' relationship
                    'last_name' => $employee->user->last_name,  // Access 'last_name' from the 'user' relationship
                    'status' => $employee->user->status,  // Access 'last_name' from the 'user' relationship
                    'email' => $employee->user->email,  // Access 'email' from the 'user' relationship
                    'address' => $employee->user->address,  // Access 'address' from the 'user' relationship
                    'phone' => $employee->userDetail->phone,  // Access 'phone' from the 'userDetail' relationship
                    'employee_id' => $employee->employee_id,
                    'date_of_birth' => $employee->date_of_birth,  // Access 'dob' from the 'userDetail' relationship
                    'line_manager' => $employee->user->line_manager,  // Access 'line_manager' from the 'user' relationship
                    'designation' => $employee->user->designation,  // Access 'designation' from the 'user' relationship
                    'joining_date' => $employee->joining_date,  // Access 'joining_date' from the 'user' relationship
                    'profile_photo' => isset($employee->userDetail->profile_photo) ? url($employee->userDetail->profile_photo) : null,  // Add profile photo URL
                ],
                'leaves_data' => [
                    'user_id' => $employee->user_id,
                    'emp_name' => $employee->user->first_name . ' ' . $employee->user->last_name,  // Concatenate first and last name
                    'leave_data' => $employee->leaves ? $employee->leaves->leave_data : null,
                    'overall_total_leaves' => $employee->leaves->overall_total_leaves ?? 0,
                    'taken' => $employee->leaves->taken ?? 0,
                    'pending' => $employee->leaves->pending ?? 0,
                    //'updated_at' => $employee->leaves->updated_at,
                    //'created_at' => $employee->leaves->created_at,
                ],
                'salary_data' => [
                    'user_id' => $employee->user_id,
                    'employee_name' => $employee->user->first_name . ' ' . $employee->user->last_name,  // Concatenate first and last name
                    'basic_salary' => $employee->salary ? $employee->salary->basic_salary : null,
                    'house_rent' => $employee->salary ? $employee->salary->house_rent : null,
                    'medical_allowance' => $employee->salary ? $employee->salary->medical_allowance : null,
                    'tax' => $employee->salary ? $employee->salary->tax : null,
                    'leave_deduction' => $employee->salary ? $employee->salary->leave_deduction : null,
                    'pf' => $employee->salary ? $employee->salary->pf : null,
                    'employee_state' => $employee->salary ? $employee->salary->employee_state : null,
                    'insurance' => $employee->salary ? $employee->salary->insurance : null,
                    'extra_working' => $employee->salary ? $employee->salary->extra_working : null,
                    'gross_total' => $employee->salary ? $employee->salary->gross_total : null,
                    'final_total' => $employee->salary ? $employee->salary->final_total : null,
                    'gross_salary' => $employee->salary ? $employee->salary->gross_salary : null,
                    'bank_name' => $employee->salary ? $employee->salary->bank_name : null,
                    'bank_ifsc' => $employee->salary ? $employee->salary->bank_ifsc : null,
                    'account_number' => $employee->salary ? $employee->salary->account_number : null,
                    'account_holder_name' => $employee->salary ? $employee->salary->account_holder_name : null,
                    //'updated_at' => $employee->salary ? $employee->salary->updated_at : null,
                    //'created_at' => $employee->salary ? $employee->salary->created_at : null,
                ]
            ]
        ];
    
        return response()->json($responseData);
    }
    public function get_birthdays(Request $request) {
        $today = Carbon::today();
    
        // Define how many records you want per page (e.g., 10)
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
    
        // Fetch employee data along with the related user data using eager loading
        $employees = Employee::with('user') // Eager load the 'user' relationship
            ->orderByRaw("MONTH(date_of_birth), DAY(date_of_birth)") // Sort by month and day
            ->get()
            ->map(function($employee) use ($today) {
                // Combine first_name and last_name as 'Employee name'
                $employee_name = $employee->first_name . '.' . $employee->last_name;
    
                // Parse the birthday date
                $birthday = Carbon::parse($employee->date_of_birth);
                $birthday->year = $today->year;
    
                // If the birthday already passed this year, set it to next year
                if ($birthday->lessThan($today)) {
                    $birthday->addYear();
                }
    
                // Format the birthday to 'dd-mm-yyyy' format
                $formatted_birthday = $birthday->format('d-m-Y');
    
                // Get employee_id, joining_date, and designation from the Employee model
                $employee_id = $employee->employee_id; // Access employee_id directly from the Employee model
                $joining_date = $employee->joining_date ? Carbon::parse($employee->joining_date)->format('d-m-Y') : 'N/A'; // Fetch from Employee model and format it
                $designation = $employee->designation; // Access designation directly from the Employee model
    
                // Return the data in the required format
                return [
                    'employee_name' => $employee_name,
                    'employee_id' => $employee_id, // From the 'employees' table
                    'designation' => $designation,
                    'joining_date' => $joining_date, // From the 'employees' table
                    'birthday' => $formatted_birthday
                ];
            })
            ->sortBy('birthday') // Sort by birthday (adjusted year)
            ->values(); // Reindex the collection to return an array of objects
    
        // Retrieve the page number from the request
        $page = $request->get('page', 1); // Defaults to page 1 if not specified
    
        // Paginate the collection manually
        $paginatedEmployees = new \Illuminate\Pagination\LengthAwarePaginator(
            $employees->forPage($page, $perPage), // Get the data for the current page
            $employees->count(), // Total count of employees
            $perPage, // Number of items per page
            $page, // Current page
            ['path' => url()->current()] // Maintain current URL for pagination links
        );
    
        // Convert paginated data to array
        $paginatedEmployeesArray = $paginatedEmployees->toArray();
    
        // Check if no employees were found
        if (empty($paginatedEmployeesArray['data'])) {
            return response()->json([
                'result' => false,
                'message' => 'No upcoming birthdays found!',
                'EmployeeDetails' => []
            ]);
        }
    
        // Return the list of employees with upcoming birthdays as an array
        return response()->json([
            'result' => true,
            'message' => 'User data retrieved successfully!',
            'EmployeeDetails' => array_values($paginatedEmployeesArray['data']), // Reindex to ensure it’s a clean array
            'pagination' => [
                'total' => $paginatedEmployees->total(), // Use $paginatedEmployees here
                'current_page' => $paginatedEmployees->currentPage(),
                'per_page' => $paginatedEmployees->perPage(),
                'last_page' => $paginatedEmployees->lastPage(),
                'from' => $paginatedEmployees->firstItem(),
                'to' => $paginatedEmployees->lastItem(),
            ]
        ]);
    }
    
    
    
    
    
    public function get_anniversaries(Request $request) {
        try {
            $today = Carbon::today();
            
            // Log today's date for debugging purposes
            \Log::info('Today date:', ['date' => $today]);
    
            // Define the number of items per page, you can change this number or make it dynamic
            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $page = $request->input('page', 1);

            // Fetch employee data along with the related user data using eager loading
            $employeesPaginator = Employee::with('user') // Eager load the 'user' relationship
                ->orderByRaw("MONTH(joining_date), DAY(joining_date)") // Sort by month and day of joining_date
                ->paginate($perPage, ['*'], 'page', $page); // Apply pagination
    
            // If no employees are found, log and return an appropriate response
            if ($employeesPaginator->isEmpty()) {
                \Log::warning('No employees found for anniversaries');
                return response()->json((object)[
                    'result' => false,
                    'message' => 'No upcoming anniversaries found!',
                    'EmployeeDetails' => []
                ]);
            }
    
            // Process employees and map anniversary details
            $employees = $employeesPaginator->getCollection()->map(function($employee) use ($today) {
                // Check if joining_date exists, log any invalid data
                if (!$employee->joining_date) {
                    \Log::warning('Employee with missing joining_date', ['employee_id' => $employee->employee_id]);
                }
    
                // Combine first_name and last_name as 'Employee name'
                $employee_name = $employee->first_name . '.' . $employee->last_name;
    
                // Parse the joining date
                $anniversary = Carbon::parse($employee->joining_date);
                $anniversary->year = $today->year;
    
                // If the anniversary already passed this year, set it to next year
                if ($anniversary->lessThan($today)) {
                    $anniversary->addYear();
                }
    
                // Format the anniversary to 'dd-mm-yyyy' format
                $formatted_anniversary = $anniversary->format('d-m-Y');
    
                // Get employee_id, designation from the Employee model
                $employee_id = $employee->employee_id; // Access employee_id directly from the Employee model
                $joining_date = $employee->joining_date ? Carbon::parse($employee->joining_date)->format('d-m-Y') : 'N/A'; // Fetch from Employee model and format it
                $designation = $employee->designation; // Access designation directly from the Employee model
    
                // Return the data in the required format
                return [
                    'employee_name' => $employee_name,
                    'employee_id' => $employee_id, // From the 'employees' table
                    'designation' => $designation,
                    'joining_date' => $joining_date, // From the 'employees' table
                    'anniversary' => $formatted_anniversary
                ];
            })
            ->sortBy('anniversary') // Sort by anniversary (adjusted year)
            ->values(); // Reindex the collection to return an array of objects
    
            // Return the paginated list of employees with upcoming anniversaries
            return response()->json((object)[
                'result' => true,
                'message' => 'Employee anniversaries retrieved successfully!',
                'EmployeeDetails' => $employees,
                 'pagination' => [
                    'total' => $employeesPaginator->total(),
                    'current_page' => $employeesPaginator->currentPage(),
                    'per_page' => $employeesPaginator->perPage(),
                    'last_page' => $employeesPaginator->lastPage(),
                    'from' => $employeesPaginator->firstItem(),
                    'to' => $employeesPaginator->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            // Log the exception for further debugging
            \Log::error('Error retrieving anniversaries', ['error' => $e->getMessage()]);
    
            // Return a 500 error response
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    
    public function getOvertimeRecords(Request $request){
        // Validate that the user_id parameter is provided and is a valid integer
        $userId = auth()->user()->id;
        $validator = Validator::make($request->all(), [
            'user_id' => 'integer|exists:users,id', // Ensure the user ID exists in the 'users' table
        ]);

        // If validation fails, return error response with validation details
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 500);
        }

        // Get the user ID from the request

        // Retrieve the overtime records for the specified user
        $overtimeRecords = OvertimeRecord::where('user_id', $userId)->get();

        // Check if records exist
        if ($overtimeRecords->isEmpty()) {
            return response()->json([
                'message' => 'No overtime records found for this user.'
            ], 200);
        }

        // If records are found, return them with a success response
        return response()->json([
            'message' => 'Overtime records retrieved successfully.',
            'data'    => $overtimeRecords
        ], 200);
    }

    public function storeOvertime(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'overtime_date'    => 'required|date_format:d-m-Y', // Ensure the format is DD-MM-YYYY
            'working_hours'    => 'required|numeric|min:1|max:24', // Ensure working hours are between 1 and 24
            'salary_per_hour'  => 'required|numeric|min:1', // Ensure salary per hour is positive
            'final_balance'    => 'required|numeric|min:0', // Ensure final balance is non-negative
            'project_name'     => 'required|string|max:255',
            'project_url'      => 'required',
            'screenshot'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate screenshot image
            'filename'         => 'nullable|string|max:255', // The user-chosen filename (e.g., test.png)
        ]);

        // If validation fails, return error response with details
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }

        // Convert the overtime_date to the correct format (YYYY-MM-DD)
        $overtimeDate = Carbon::createFromFormat('d-m-Y', $request->input('overtime_date'))->format('Y-m-d');
        
        // Get the authenticated user ID
        $userId = auth()->user()->id;

        // Check if an overtime record already exists for the current day
        $existingRecord = OvertimeRecord::where('user_id', $userId)
                                        ->where('overtime_date', $overtimeDate)
                                        ->first();

        if ($existingRecord) {
            // If a record already exists for today, return an error message
            return response()->json([
                'status'=> false,
                'error' => 'Record already added for today. You can only update the existing record.'
            ], 400);
        }

        // Handle screenshot file upload (if provided)
        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            // Check if a valid file is uploaded
            $file = $request->file('screenshot');

            if ($file->isValid()) {
                // Define the base URL for your system (this can be kept static if it is constant)
                $baseUrl = 'https://hrmsdb.spartanstaging.site/public/overtime-records/';

                // Define the folder path where the user's screenshots will be stored
                $folderPath = public_path('overtime-records/' . $userId);

                // Create the directory if it doesn't exist
                if (!File::exists($folderPath)) {
                    File::makeDirectory($folderPath, 0755, true);
                }

                // Get the user-chosen filename, or use the original filename if not provided
                $filename = $request->input('filename') ?: $file->getClientOriginalName();

                // Append the current timestamp to the filename to ensure uniqueness
                $timestamp = now()->format('YmdHis'); // Format current timestamp as YmdHis
                $uniqueFilename = $timestamp . '_' . $filename; // Add timestamp to filename

                // Construct the full file path (without the base URL, this is just the relative path)
                $relativeFilePath = 'overtime-records/' . $userId . '/' . $uniqueFilename;

                // Full URL including the base URL
                $fileUrl = $baseUrl . $userId . '/' . $uniqueFilename;

                // Check if the file already exists in the user's folder
                if (File::exists($folderPath . '/' . $uniqueFilename)) {
                    return response()->json(['error' => 'File with this name already exists. Please choose a different name.'], 400);
                }

                // Store the file in the user's folder
                $file->move($folderPath, $uniqueFilename);

                // The $fileUrl will be the URL you can store in your database or use to display the image
                $screenshotPath = $fileUrl; // Store the full URL in the DB
            } else {
                return response()->json(['error' => 'Uploaded file is not valid.'], 400);
            }
        }

        // Create and save the overtime record using Eloquent
        $overtimeRecord = new OvertimeRecord([
            'user_id'        => auth()->user()->id, // Get the authenticated user ID
            'overtime_date'  => $overtimeDate, // Use the correctly formatted date
            'working_hours'  => $request->input('working_hours'),
            'salary_per_hour'=> $request->input('salary_per_hour'),
            'final_balance'  => $request->input('final_balance'),
            'project_name'   => $request->input('project_name'),
            'project_url'    => $request->input('project_url'),
            'screenshot'     => $screenshotPath, // Store the unique file URL in the DB
        ]);

        // Save the record to the database
        if ($overtimeRecord->save()) {
            return response()->json([
                'message' => 'Overtime record added successfully.',
                'data'    => $overtimeRecord
            ], 201);
        } else {
            return response()->json(['error' => 'Failed to save overtime record'], 500);
        }
    }

    public function updateOvertimeRecord(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'overtime_date'    => 'required|nullable|date_format:d-m-Y',
            'working_hours'    => 'nullable|numeric|min:1|max:24',
            'salary_per_hour'  => 'nullable|numeric|min:1',
            'final_balance'    => 'nullable|numeric|min:0',
            'project_name'     => 'nullable|string|max:255',
            'project_url'      => 'nullable|string|max:255',
            //'screenshot'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filename'         => 'nullable|string|max:255',
            'status'           => 'nullable', // HR updates status to approved/rejected
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }

        // Get the authenticated user ID
        $userId = auth()->user()->id;

        // Find the overtime record for the authenticated user by overtime_date
        $overtimeDate = Carbon::createFromFormat('d-m-Y', $request->input('overtime_date'))->format('Y-m-d');
        $overtimeRecord = OvertimeRecord::where('user_id', $userId)
                                        ->where('overtime_date', $overtimeDate)
                                        ->first();

        if (!$overtimeRecord) {
            return response()->json(['error' => 'Overtime record not found for this user on the given date.'], 404);
        }

        // Prepare the updated data for the overtime record
        $updatedData = ['user_id' => $userId];
        // echo  $overtimeRecord->status;die; 
        // Only update status to 'pending' if the current status is null
        if ($overtimeRecord->status == null || $overtimeRecord->status == NULL ) {
            $updatedData['status'] = 'pending'; 
        }

        // Update other fields if provided
        if ($request->has('overtime_date')) {
            $updatedData['overtime_date'] = Carbon::createFromFormat('d-m-Y', $request->input('overtime_date'))->format('Y-m-d');
        }

        if ($request->has('working_hours')) {
            $updatedData['working_hours'] = $request->input('working_hours');
        }

        if ($request->has('salary_per_hour')) {
            $updatedData['salary_per_hour'] = $request->input('salary_per_hour');
        }

        if ($request->has('final_balance')) {
            $updatedData['final_balance'] = $request->input('final_balance');
        }

        if ($request->has('project_name')) {
            $updatedData['project_name'] = $request->input('project_name');
        }

        if ($request->has('project_url')) {
            $updatedData['project_url'] = $request->input('project_url');
        }

        // Handle screenshot file upload only if a new screenshot is provided
        if ($request->hasFile('screenshot')) {
            
            $file = $request->file('screenshot');
            if ($file->isValid()) {
                // Append a timestamp to the filename to ensure uniqueness
                $timestamp = now()->format('YmdHis'); // Get the current timestamp
                $filename = $timestamp . '_' . ($request->input('filename') ?: $file->getClientOriginalName());

                $baseUrl = 'https://hrmsdb.spartanstaging.site/public/overtime-records/'; // The base URL
                $folderPath = public_path('overtime-records/' . $userId);

                if (!File::exists($folderPath)) {
                    File::makeDirectory($folderPath, 0755, true);
                }

                $fileUrl = $baseUrl . $userId . '/' . $filename; // Full URL with the base URL

                // Optionally, delete the old file if it's being replaced
                if (File::exists(public_path($overtimeRecord->screenshot))) {
                    File::delete(public_path($overtimeRecord->screenshot)); // Delete the old image
                }

                // Store the new file in the user's folder
                $file->move($folderPath, $filename);
                $updatedData['screenshot'] = $fileUrl; // Store the full URL in the DB
            } else {
                return response()->json(['error' => 'Uploaded file is not valid.'], 500);
            }
        } else {
            // If no new image is uploaded, keep the original image unchanged
            $updatedData['screenshot'] = $overtimeRecord->screenshot; // Use the existing screenshot URL
        }

        // Update the existing record
        $overtimeRecord->update($updatedData);

        return response()->json([
            'message' => 'Overtime record updated successfully.',
            'data'    => $overtimeRecord
        ], 200);
    }

    public function overtimeRecordStatus(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id', // assuming user_id must exist in users table
            'overtime_date' => 'required|string|date_format:d-m-Y', // ensure date format is DD-MM-YYYY
            'status' => 'required|string', // assuming status is a string like 'approved', 'pending', etc.
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        try {
            // Check if the date is in correct format before converting
            $dateString = $request->overtime_date;
            $formattedDate = Carbon::createFromFormat('d-m-Y', $dateString);

            // Now convert the date to the standard Y-m-d format
            $formattedDate = $formattedDate->format('Y-m-d');

            // Find the overtime record by user_id and the formatted date
            $overtimeRecord = OvertimeRecord::where('user_id', $request->user_id)
                                            ->where('overtime_date', $formattedDate)
                                            ->first();

            // If the record is not found, return an error response
            if (!$overtimeRecord) {
                return response()->json([
                    'error' => 'Overtime record not found.',
                ], 404);
            }

            // Update the record status
            $overtimeRecord->status = $request->status;
            $overtimeRecord->save();

            // Return the updated record as a response
            return response()->json([
                'message' => 'Overtime record updated successfully.',
                'data' => $overtimeRecord,
            ], 200);

        } catch (\Exception $e) {
            // If there is an issue with date conversion, return a more specific error
            return response()->json([
                'error' => 'Invalid date format provided.',
                'details' => $e->getMessage(),
            ], 400);
        }
    }

    public function getEmployeesOvertimeRecords(){
        $records = OvertimeRecord::all();
        return response()->json([   
            'result' => true,
            'message' => 'Get overtime records data',
            "records" => $records
        ]);
    }


    public function createNotice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required',
            'attachment' => 'nullable|max:10240',  // max 10MB
            'email' => 'required|string',  // Validate the email field as a comma-separated string of valid emails
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->all()
            ], 400);
        }

        // Handle file upload for attachment
        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            // Store the file directly in the public directory under 'notices' folder
            $attachmentPath = 'notices-media/' . $file->getClientOriginalName(); // Name the file as its original name
            $file->move(public_path('notices-media'), $attachmentPath); // Move the file to the public/notices folder

            // Generate the full URL to the stored file
            $attachmentUrl = url('public/' . $attachmentPath); // This will give you the full URL (https://your-site-url/storage/notices/test.png)
        }


        // Create the new notice record in the database
        $notice = Notice::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'attachment' => isset($attachmentUrl) ? $attachmentUrl : null,  // Save the full URL to the attachment
            'status' => $request->input('status'),
            'email' => $request->input('email'),  // Store the email as a string (comma-separated)
        ]);

        // Convert the email string to an array
        $emails = explode(',', $request->input('email'));

        // Trim whitespace and remove any empty values
        $emails = array_map('trim', $emails);
        $emails = array_filter($emails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        // Send the email to all recipients at once
        try {
            Mail::html(
                "<html>
                    <body>
                        <h1>Notice</h1>
                        <p>" . $request->input('title') . "</p>
                        <p>" . $request->input('description') . "</p>
                    </body>
                </html>",
                function ($message) use ($emails) {
                    $message->to($emails)
                            ->subject('New Notice Created');
                }
            );

            // Log success message for debugging
            \Log::info('Emails sent successfully to: ' . implode(',', $emails));
        } catch (\Exception $e) {
            // Log error message for debugging
            \Log::error('Failed to send emails: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send emails.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Return a successful response with the created notice data
        return response()->json([
            'message' => 'Notice created and emails sent successfully',
            'data' => $notice,
        ], 201);
    }


    public function getEmails()
    {
        // Fetch all emails from Employee model
        $emails = Employee::pluck('email');
        
        // Return a structured JSON response
        return response()->json([
            'status' => 'success',
            'data' => $emails
        ]);
    }

    public function getNotices(Request $request)
    {
        // Get the email of the currently authenticated user
        $userEmail = auth()->user()->email;
        
        // Fetch all notices where the user's email is part of the 'email' field (comma-separated)
        $notices = Notice::whereRaw('CONCAT(",", email, ",") LIKE ?', ['%' . $userEmail . '%'])->get();

        // If there are no notices, return a message indicating no notices
        if ($notices->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No notices found for this user.',
            ]);
        }

        // Return the notices in a structured JSON response
        return response()->json([
            'status' => 'success',
            'data' => $notices
        ]);
    }

    public function getAllNotices()
    {
        $notices = Notice::all();

        if ($notices->isEmpty()) {
            return response()->json(['message' => 'No notices found.'], 404);
        }

        return response()->json([
            'message' => 'All notices retrieved successfully.',
            'data'    => $notices
        ], 200);
    }

    // public function get_allemployee(Request $request)
    // {
    //     // Get the search query from the request if it's present
    //     $searchQuery = $request->input('search_query', null);
    
    //     // Build the query
    //     $query = Employee::with(['userDetail', 'leaves', 'salary']);
    
    //     // If a search query is provided, apply the filter for employee_id
    //     if ($searchQuery) {
    //         $query->whereHas('user', function ($query) use ($searchQuery) {
    //             // Apply case-insensitive search
    //             $query->whereRaw('LOWER(employee_id) LIKE ?', ['%' . strtolower($searchQuery) . '%']);
    //         });
    //     }
    
    //     // Get the employees (either all or filtered based on search query)
    //     $employe_data = $query->get();
    
    //     // Transform the employee data to remove redundancy and properly format the response
    //     $employe_data->transform(function ($employee) {
    //         // Add profile photo if it exists
    //         if (isset($employee->userDetail) && isset($employee->userDetail->profile_photo)) {
    //             // Build the URL for the profile photo (assuming profile_photo contains the path relative to the 'public' directory)
    //             $employee->employeeprofile = url('public/' . $employee->userDetail->profile_photo);
    //         } else {
    //             $employee->employeeprofile = null;
    //         }
    
    //         // Decode leave data if it's in string format
    //         if (isset($employee->leaves) && !empty($employee->leaves->leave_data)) {
    //             if (is_string($employee->leaves->leave_data)) {
    //                 $employee->leaves->leave_data = json_decode($employee->leaves->leave_data);
    //             }
    //         }
    
    //         // Ensure salary data is only included once
    //         if (isset($employee->salary)) {
    //             $employee->salary_data = $employee->salary; // Add salary info under a single field
    //         } else {
    //             $employee->salary_data = null; // No salary info if it's not available
    //         }
    
    //         // Remove the redundant salary relationship field (salary), keeping only salary_data
    //         unset($employee->salary);
    
    //         return $employee;
    //     });
    
    //     // Return the response with the employee data
    //     return response()->json([
    //         'result' => true,
    //         'message' => $searchQuery ? 'Search Employee Data' : 'Get All Employee Data',
    //         'employee' => $employe_data
    //     ]);
    // }
    public function get_allemployee(Request $request)
{
    // Get the search query from the request if it's present
    $searchQuery = $request->input('search_query', null);

    // Get the number of items per page (default to 10 if not provided)
    $perPage = $request->input('per_page', 10);

    // Get the current page (default to 1 if not provided)
    $page = $request->input('page', 1);

    // Build the query
    $query = Employee::with(['userDetail', 'leaves', 'salary']);

    // If a search query is provided, apply the filter for employee_id
    if ($searchQuery) {
        $query->whereHas('user', function ($query) use ($searchQuery) {
            // Apply case-insensitive search
            $query->whereRaw('LOWER(employee_id) LIKE ?', ['%' . strtolower($searchQuery) . '%']);
        });
    }

    // Get the employees with pagination
    $employe_data = $query->paginate($perPage, ['*'], 'page', $page);

    // Transform the employee data to remove redundancy and properly format the response
    $employe_data->getCollection()->transform(function ($employee) {
        // Add profile photo if it exists
        if (isset($employee->userDetail) && isset($employee->userDetail->profile_photo)) {
            // Build the URL for the profile photo (assuming profile_photo contains the path relative to the 'public' directory)
            $employee->employeeprofile = url('public/' . $employee->userDetail->profile_photo);
        } else {
            $employee->employeeprofile = null;
        }

        // Decode leave data if it's in string format
        if (isset($employee->leaves) && !empty($employee->leaves->leave_data)) {
            if (is_string($employee->leaves->leave_data)) {
                $employee->leaves->leave_data = json_decode($employee->leaves->leave_data);
            }
        }

        // Ensure salary data is only included once
        if (isset($employee->salary)) {
            $employee->salary_data = $employee->salary; // Add salary info under a single field
        } else {
            $employee->salary_data = null; // No salary info if it's not available
        }

        // Remove the redundant salary relationship field (salary), keeping only salary_data
        unset($employee->salary);

        return $employee;
    });

    // Return the response with the employee data
    return response()->json([
        'result' => true,
        'message' => $searchQuery ? 'Search Employee Data' : 'Get All Employee Data',
        'employee' => $employe_data,
        'pagination' => [
            'total' => $employe_data->total(),
            'current_page' => $employe_data->currentPage(),
            'per_page' => $employe_data->perPage(),
            'last_page' => $employe_data->lastPage(),
            'from' => $employe_data->firstItem(),
            'to' => $employe_data->lastItem(),
        ]
    ]);
}
public function export_employee(Request $request){
    // $query = Employee::with(['userDetail', 'leaves', 'salary']);
    // downloafd excel sheet showing emplo
}
private function syncEmployeeToOrpect($data)
{
    $url = 'https://spartanbots.xyz/borpact/public/api/hrms-sync-employee';

    $payload = [

        'empId' => $data['employee_id'],

        'empName' => trim(
            ($data['first_name'] ?? '') . ' ' .
            ($data['last_name'] ?? '')
        ),

        'email' => $data['email'],

        'phone' => $data['phone'],

        'position' => $data['role'],

        'dateOfJoining' => $data['joining_date'],

        'dateOfBirth' => $data['date_of_birth'] ?? null,

        'state' => $data['employee_state'] ?? null,

        'current_salaray' => $data['basic_salary'] ?? 0,

        'tax_number' => null,
        'permanentAddress' => null,
        'city' => null,
        'country' => null,
        'postalCode' => null,
        'linkedIn' => null,
        'increment_date' => null,
        'last_increment_date' => null,
        'pan_number' => null,

        'added_by' => $data['added_by'],

        'hrms_employee_id' => $data['employee_id']
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {

        dd([
            'Curl Error' => curl_error($ch)
        ]);
    }

    curl_close($ch);

    // dd([
    //     'HTTP_CODE' => $httpCode,
    //     'RAW_RESPONSE' => $response,
    //     'JSON_RESPONSE' => json_decode($response, true),
    //     'PAYLOAD' => $payload
    // ]);
}


}