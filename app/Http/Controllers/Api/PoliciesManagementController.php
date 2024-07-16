<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\PoliciesManagement;    
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class PoliciesManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $policies =  PoliciesManagement::all();
        return response()->json([
         'result' => true,
         'message' => 'Policies Lists.',
         'data'=>$policies,
        ]);
    }

     /** 
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'exception' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $policies = $this->store($request->all());
        if ($policies) {
            return response()->json([
                'result' => true,
                'message' => 'Policies Created successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Policies Created failed");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        $user = auth()->user();
        $project_data = PoliciesManagement::create([
            'name' => $data['name'],
            'user_id' => $user->id,
        ]);
         return $project_data;
    }
    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
           
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,$id)
    {
        $user = auth()->user();
        $projects =  PoliciesManagement::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'project detail data',
         'data'=>$projects,
     ]);
    }
    public function update_data($data)
    {
        $project_data = PoliciesManagement::where('id',$data['id'])->update([
            'name' => $data['name'],
        ]);
         return $project_data;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $projects = $this->update_data($request->all());
        if ($projects) {
            return response()->json([
                'result' => true,
                'message' => 'Project Updated Successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Updated Failed");
        }
    }

}
