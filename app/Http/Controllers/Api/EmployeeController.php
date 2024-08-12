<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Employee;
use App\Models\User;
use App\Models\JobDetail;
use App\Models\User_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    # make new registration here
    protected function store(array $data)
    {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'status' => 'employee',
                'password' => Hash::make($data['password']),
            ]);
             $employee = Employee::create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'line_manager' => $data['line_manager'],
                'designation' => $data['designation'],
                'employee_id' =>$data['employee_id'],
                'email' => $data['email'],
                'joining_date' => $data['joining_date'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
           // set guest_user_id to user_id from carts
            return $user;
        }
        return null;
    }
   

    # register new customer here
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
        ]);
         
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        

        
        $user = $this->store($request->all());
        # verification
        if ($user) {
                    
                        return response()->json([
                            'result' => true,
                            'message' => 'Employee are created successful.',
                            'access_token' => '',
                            'token_type' => ''
                        ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }

    public function update(Request $request)
    {

           $id = $request->id;
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'id' => 'required',
        ]);
         
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }


        
        $user = $this->updated_data($request->all());
        # verification
        if ($user) {
                    
                        return response()->json([
                            'result' => true,
                            'message' => 'Employee Data Updated',
                            'access_token' => '',
                            'token_type' => ''
                        ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }
    protected function updated_data(array $data)
    {

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        
        $employee_data = Employee::where('id',$data['id'])->first();
            $user = User::where('id',$employee_data->user_id)->update([
                'name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'status' => 'employee',
                'password' => Hash::make($data['password']),
            ]);
             $employee = Employee::where('id',$data['id'])->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'line_manager' => $data['line_manager'],
                'designation' => $data['designation'],
                'employee_id' =>$data['employee_id'],
                'email' => $data['email'],
                'joining_date' => $data['joining_date'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
           // set guest_user_id to user_id from carts
            return $user;
        }
        return null;
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
       
    public function get_allemployee(Request $request){
      
        $employe_data =  Employee::all();
        return response()->json([   
            'result' => true,
            'message' => 'Get Employee Data',
            "employee"=>$employe_data
        ]);
    }
    public function delete_employee(Request $request){
        $id = $request->id;
        $employee = Employee::where('id',$id)->first();
        User::where('user_id',$employee->user_id)->delete();
        JobDetail::where('user_id',$employee->user_id)->delete();
        Employee::where('id',$id)->delete();
        return response()->json([   
            'result' => true,
            'message' => 'Employee all Data are Deleted',
        ]);
    }
}

