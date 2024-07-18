<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\User_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{



    # make new registration here
    protected function create(array $data)
    {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'organisation' => $data['organisation'],
                'organisation_id' => $data['organisation_id'],
                'address' => $data['address'],
                'payment' => $data['payment'],
                'email_verified_at' =>'',
                'email' => $data['email'],
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
   

    # register new customer here
    public function register(Request $request)
    {

       
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $request->email)->first() != null) {

                return $this->registrationFailed('Email already exists.');
            }
        }
        
        if($request->password != $request->confirm_password){
            return $this->registrationFailed('please enter same password and conform password');
        }
      

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }


        
        $user = $this->create($request->all());
        # verification
        if ($user) {
                    
                        return response()->json([
                            'result' => true,
                            'message' => 'Registration successful.',
                            'access_token' => '',
                            'token_type' => ''
                        ]);
        } else {
            return $this->registrationFailed("Registration failed");
        }
    }


    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user != null) {
                if (Hash::check($request->password, $user->password)) {

                    // if ($user->email_verified_at == null) {
                    //     return $this->loginFailed('Please verify your account');
                    // }
                    return $this->loginSuccess($user);
                } else {
                    return $this->loginFailed('Unauthorized');
                }
           
        } else {
            return $this->loginFailed('User not found');
        }
    }

    protected function loginSuccess($user)
    {
        $token = $user->createToken('API Token')->plainTextToken;
        return response()->json([
            'result' => true,
            'message' => 'Successfully logged in',
            'access_token' => $token,
            'token_type' => 'Bearer',
            "user"=>[
                'name' => $user->name,
                'last_name' => $user->last_name,
                'orgaisation' => $user->orgaisation,
                'organisation_id' => $user->organisation_id,
                'address' => $user->address,
                'payment' => $user->payment,
                'email' => $user->email,
                
            ]
        ]);
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

    public function checkToken(Request $request)
    {

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
    public function logout(Request $request)
    {

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
            "user"=>[
               'name' => '',
                'last_name' =>'' ,
                'orgaisation' =>'' ,
                'organisation_id' => '',
                'address' => '',
                'payment' => '',
                'email' => '',
            ]
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
                  'name' => $user->name,
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
    public function update_profile(Request $request){
        $user_id = auth()->user()->id;
       
             
        User_Detail::where('user_id',$user_id)->update([
            'gender' => $request->gender,
            'dob' => $request->dob,
            'job_title' => $request->job_title,
            'department' => $request->department,
            'joining_date' => $request->joining_date,
            'emp_id' => $request->emp_id,
            'profile_photo' => $photoPath,
            'phone' => $request->phone,
            'tax_number' =>  $request->tax_number,
        ]);
                              
        $user =  User::where('id',$user_id)->with('userDetail')->first();
        $photopathurl =  optional($user->userDetail)->profile_photo;
        return response()->json([
            'result' => true,
            'message' => 'Update Profile Successfully',
            "user"=>[
                'name' => $user->name,
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
    public function get_profile(Request $request){
        $user_id = auth()->user()->id;
        $user =  User::where('id',$user_id)->with('userDetail')->first();
        $photopathurl =  optional($user->userDetail)->profile_photo;
        return response()->json([   
            'result' => true,
            'message' => 'Get Profile Data',
            "user"=>[
                'name' => $user->name,
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
                'profile_photo' => $photopathurl ? url($photopathurl) : null,
                'phone' => optional($user->userDetail)->phone,
                'tax_number' =>  optional($user->userDetail)->tax_number,
            ]
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
    public function address(Request $request)
    {
        $user_id = auth()->user()->id;
       
             
        User_Detail::where('user_id',$user_id)->update([
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zipcode' => $request->zipcode
        ]);
        return response()->json([   
            'result' => true,
            'message' => 'User Address Data updated',
            
        ]);
    }
    public function emergency_contact(Request $request)
    {
        $user_id = auth()->user()->id;
       
             
        User_Detail::where('user_id',$user_id)->update([
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

