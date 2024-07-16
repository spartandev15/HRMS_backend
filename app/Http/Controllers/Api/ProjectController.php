<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\Project;    
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $projects =  Project::all();
        return response()->json([
         'result' => true,
         'message' => 'project Lists.',
         'data'=>$projects,
     ]);
    }

     /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $projects = $this->store($request->all());
        if ($projects) {
            return response()->json([
                'result' => true,
                'message' => 'Project Created successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Project Created failed");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        $user = auth()->user();
        $project_data = Project::create([
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
        $projects =  project::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'project detail data',
         'data'=>$projects,
     ]);
    }
    public function update_data($data)
    {
        $project_data = Project::where('id',$data['id'])->update([
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
