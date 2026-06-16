<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\User_Detail;
use Illuminate\Validation\Rules\Password;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File; // Import the File facade
use App\Models\Leave;

class AuthController extends Controller{
  
    protected function create(array $data) {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'organisation' => $data['organisation'],
                'organisation_id' => $data['organisation_id'],
                'address' => $data['address'],
                'payment' => $data['payment'],
                'email_verified_at' =>'',
                'email' => $data['email'],
                'status'=>'owner',
                // 'phone' => validatePhone($data['phone']),
                'password' => Hash::make($data['password']),
            ]);
            User_Detail::create([
                'user_id'=>$user->id,
            ]);
            // set guest_user_id to user_id from carts
            return $user;
        }
        return null;
    }
    
    public function register(Request $request){       
       // echo "<pre>";print_r($request->all());die;
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $request->email)->first() != null) {
                return $this->registrationFailed('Email already exists.');
            }
        }
        if($request->password != $request->confirm_password){
            return $this->registrationFailed('Password and confirm password should be same!');
        }                
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'password' => [
                    'required',
                    'string',
                    Password::min(8)
                        ->mixedCase()
                        ->letters()
                        ->numbers()
                        ->symbols(),
                ],
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }             
        $user = $this->create($request->all());
        # verification
        if ($user) {                   
            return response()->json([
                'result' => true,
                'message' => 'User registered successfully.',
            ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }
    protected function StorecreateAPI(array $data) {
        // if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $user = User::updateOrCreate(
            ['email' => $data['email']], // Search condition
            [
                'first_name'      => $data['first_name'],
                'last_name'       => $data['last_name'],
                'organisation'    => $data['organisation'],
                'organisation_id' => $data['organisation_id'],
                'address'         => $data['address'],
                'payment'         => $data['payment'],
                'orpect_user_id'         => $data['orpect_user_id'],
                'email_verified_at' => null,
                'status'          => 'owner',
                // 'phone'        => validatePhone($data['phone']),
                'password'        => $data['password'],
            ]
        );

        // Create user detail only if it doesn't exist
        User_Detail::firstOrCreate([
            'user_id' => $user->id,
        ]);
            // set guest_user_id to user_id from carts
            return $user;
        // }
        // return null;
    }
    public function thirdpartyDatastoreAPI(Request $request){
          $user = $this->StorecreateAPI($request->all());
        //   dd($user);
        # verification
        if ($user) {                   
            return response()->json([
                'result' => true,
                'message' => 'User registered successfully.',
                'data'=>$user
            ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                // if ($user->email_verified_at == null) {
                //     return $this->loginFailed('Please verify your account');
                // }
                if($user->status== "employee"){
                    return $this->EmployeeloginSuccess($user);
                }else{
                    return $this->loginSuccess($user);
                }
            } else {
                return $this->loginFailed('Unauthorized');
            }
        } else {
            return $this->loginFailed('User not found');
        }
    }

    protected function loginSuccess($user){
        $token = $user->createToken('API Token')->plainTextToken;
        return response()->json([
            'result' => true,
            'message' => 'Logged In Successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            "user"=> $user,
                
        ]);
        
    }

    protected function EmployeeloginSuccess($user){
        $employee = Employee::where('user_id',$user->id)->first();
        $token = $user->createToken('API Token')->plainTextToken;
        return response()->json([
            'result' => true,
            'message' => 'Employee Logged In Successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            "employee"=>  $employee,
            "user"=>  $user,
        ]);
    }
  
    protected function loginFailed($message){
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

    protected function registrationFailed($message){
        return response()->json([
            'result' => false,
            'message' => $message,
            'access_token' => '',
            'token_type' => ''
        ]);
    }

    public function checkToken(Request $request){
        $false_response = [
            'result' => false,
            "user"=>   [
                'name' => "",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
            ]
        ];
        
        $token=PersonalAccessToken::findToken($request->bearerToken());
        if (!$token) {
            return response()->json($false_response);
        }
        
        $user = $token->tokenable;
        if ($user->is_banned) {
            return response()->json([
                'result' => false,
                "is_banned"=>true,
                'message' => localize("You have been banned")
            ]);
        }

        if ($user == null) {
            return response()->json($false_response);

        }

        return response()->json([
            'result' => true,
            "user"=>[
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'balance' => $user->user_balance,
                'avatar' => uploadedAsset($user->avatar)
            ]
        ]);
    }
    
    public function logout(Request $request){
       // echo "logout";die;
        $false_response = [
            'result' => false,
            "user"=>   [
                'name' => "",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
                ]
        ];
        $user = auth()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        if ($user == null) {
            return response()->json($false_response);

        }
        return response()->json([
            'result' => true,
            'message' => 'User logged out Successfully',
           
        ]);

    }
    
    public function upload_profile_image(Request $request){
        $user_id = auth()->user()->id;
        $userDetail = User_Detail::where('user_id', $user_id)->first();
        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = 'public/profile_photos/' . $photoName;
    
            // Move the file to the public/profile_photos directory
            $photo->move(public_path('profile_photos'), $photoName);
    
        } else {
            $photoPath = $userDetail->profile_photo;
        }
        
          User_Detail::where('user_id',$user_id)->update([
             'profile_photo' => $photoPath,
          ]);
          $user =  User::where('id',$user_id)->with('userDetail')->first();
          $photopathurl =  optional($user->userDetail)->profile_photo;
          return response()->json([
              'result' => true,
              'message' => 'Update Profile Successfully',
              "user"=>[
                  'first_name' => $user->name,
                  'last_name' => $user->last_name,
                  'orgaisation' => $user->orgaisation, 
                  'organisation_id' => $user->organisation_id,
                  'address' => $user->address,
                  'payment' => $user->payment, 
                  'email' => $user->email,
                  'gender' => optional($user->userDetail)->gender,
                  'dob' => optional($user->userDetail)->dob,
                  'job_title' => optional($user->userDetail)->job_title,
                  'department' => optional($user->userDetail)->department,
                  'joining_date' => optional($user->userDetail)->joining_date,
                  'emp_id' => optional($user->userDetail)->emp_id,
                  'phone' => optional($user->userDetail)->phone,
                  'tax_number' =>  optional($user->userDetail)->tax_number,
                  'profile_photo' =>  $photopathurl ? url($photopathurl) : null,
              ]
          ]); 
    }

    public function update_profile(Request $request)
    {
        $user_id = auth()->user()->id; // Get the authenticated user's ID
     //   echo "<pre>";print_r($request->all());die;
        $user = $this->updated_data($request->all(), $user_id);
    
        // If the update was successful, return a success response
        if ($user) {
            // Parse leave data into an object (currently it seems to be a string)
            $leaveData = json_decode($user->leave->leave_data);
          //  echo "<pre>";print_r($leaveData);die;
            // Include the updated user data in the response
            return response()->json([
                'result' => true,
                'message' => 'Profile Updated',
                'user' => $user,
                'leave' => $leaveData,  // Include parsed leave data
                'access_token' => '',
                'token_type' => ''
            ]);
        } else {
            // If the update fails, return a failure response
            return $this->registrationFailed("Update failed.");
        } 
}
protected function updated_data(array $data, $user_id)
{
    // Find the employee data based on the ID
    $employee_data = Employee::where('user_id', $user_id)->first();

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
        Employee::where('user_id', $user_id )->update($employee_update_data);
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
    if (!empty($data['first_name'])) $salary_update_data['employee_name'] = $data['first_name'] . ' ' . $data['last_name'];
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

    public function employeeupdate_profile(Request $request){
        $user_id = auth()->user()->id;
        $userDetail = Employee::where('user_id', $user_id)->first();
        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = 'public/profile_photos/' . $photoName;
    
            // Move the file to the public/profile_photos directory
            $photo->move(public_path('profile_photos'), $photoName);
    
        } else {
            $photoPath = $userDetail->profile_photo;
        }
       
        $employee = Employee::where('user_id',$user_id)->update([
           'profile_photo' => $photoPath,
        ]);

        return response()->json([
            'result' => true,
            'message' => 'Profile Updated Successfully',
           
        ]); 
    } 

    public function get_profile(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = auth()->user()->id;
    
        // Fetch the user's details along with employee, leave, and salary details
        $user = User::with([
            'employee',
            'leave',
            'salary'
        ])
        ->where('id', $user_id)
        ->first();
    
        // Check if user exists
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found'
            ]);
        }
    
        // Extract employee, leave, and salary data from the user object
        $employee = $user->employee;
        $leave = $user->leave;
        $salary = $user->salary;
    
        // Decode the leave_data JSON string into an object if present
        $leave_data = $leave ? json_decode($leave->leave_data) : null;
    
        // Prepare the response with the desired structure
        return response()->json([
            'result' => true,
            'message' => 'Get Profile Data',
            'user' => [
                'user_details' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'status'=> $user->status,
                    'id'=> $employee->id ?? null,
                    'address' => $employee->address ?? null,
                    'phone' => $employee->phone ?? null,
                    'employee_id' => $employee->employee_id ?? null,
                    'date_of_birth' => $employee->date_of_birth ?? null,
                    'line_manager' => $employee->line_manager ?? null,
                    'designation' => $employee->designation ?? null,
                    'joining_date' => $employee->joining_date ?? null,
                    'profile_photo' => isset($employee->profile_photo) ? url($employee->profile_photo) : null,  // Add profile photo URL
                ],
                'leaves_data' => [
                    'user_id' => $leave->user_id ?? null,
                    'emp_name' => $leave->emp_name ?? null,
                    'leave_data' => $leave_data,
                    'overall_total_leaves' => $leave->overall_total_leaves ?? null,
                    'taken' => $leave->taken ?? null,
                    'pending' => $leave->pending ?? null,
                    'updated_at' => $leave->updated_at ?? null,
                    'created_at' => $leave->created_at ?? null,
                ],
                'salary_data' => [
                    'user_id' => $salary->user_id ?? null,
                    'employee_name' => $salary->employee_name ?? null,
                    'basic_salary' => $salary->basic_salary ?? null,
                    'house_rent' => $salary->house_rent ?? null,
                    'medical_allowance' => $salary->medical_allowance ?? null,
                    'tax' => $salary->tax ?? null,
                    'leave_deduction' => $salary->leave_deduction ?? null,
                    'pf' => $salary->pf ?? null,
                    'employee_state' => $salary->employee_state ?? null,
                    'insurance' => $salary->insurance ?? null,
                    'extra_working' => $salary->extra_working ?? null,
                    'gross_total' => $salary->gross_total ?? null,
                    'final_total' => $salary->final_total ?? null,
                    'gross_salary' => $salary->gross_salary ?? null,
                    'bank_name' => $salary->bank_name ?? null,
                    'bank_ifsc' => $salary->bank_ifsc ?? null,
                    'account_number' => $salary->account_number ?? null,
                    'account_holder_name' => $salary->account_holder_name ?? null,
                    'updated_at' => $salary->updated_at ?? null,
                    'created_at' => $salary->created_at ?? null,
                ]
            ],
        ]);
    }
    
    
    
    

        
    public function upload_document(Request $request){
              $image = $request->file('document_image');
              $user_id = auth()->user()->id;
              $path = $image->store('images', 'public');
               $user = User_Detail::where('user_id',$user_id)->update([
                    'upload_document' => $path,
                ]);
                return response()->json(['message' => 'Image uploaded successfully', 'path' => $path], 201);

    }

    public function address_data(Request $request){
        $user_id = auth()->user()->id;       
        User::where('id',$user_id)->update([
            'address' => $request->address,
        ]);
        User_Detail::updateOrCreate(
            ['user_id' => $user_id],  // Condition to check if the record exists
            [
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zipcode' => $request->zipcode,
        ]);
        return response()->json([   
            'result' => true,
            'message' => 'User Address Data updated',
            
        ]);
    }

    public function emergency_contact_update(Request $request){
        $user_id = auth()->user()->id;             
        User_Detail::updateOrCreate(
            ['user_id' => $user_id],  // Condition to check if the record exists
            [
            'emergency_name' => $request->emergency_name,
            'relationship' => $request->relationship,
            'emergency_phone' => $request->emergency_phone,
            'emergency_mobileno' => $request->emergency_mobileno
        ]);
        return response()->json([   
            'result' => true,
            'message' => 'User Emergency Contact Data updated',
            
        ]);
    }
    
}

