<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\LeaveManagement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LeaveManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */  
    public function index()
    {
        $user = auth()->user();
       $leaves =  LeaveManagement::where('user_id',$user->id)->get();
       return response()->json([
        'result' => true,
        'message' => 'Leave Data successful.',
        'data'=>$leaves,
    ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type' => 'required',
            'reason' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $leaves = $this->store($request->all());
        if ($leaves) {
            return response()->json([
                'result' => true,
                'message' => 'Leaves Created successful.',
                
            ]);
        } else {
            return $this->registrationFailed("created failed");
        }
    }

    /**
     * Store a newly created resource in storage.
     */   
    public function store($data)
    {
        $user = auth()->user();
        $leaves_data = LeaveManagement::create([
                 'user_id' => $user->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'leave_type' => $data['leave_type'],
                'reason' => $data['reason'],
        ]);
         return $leaves_data;
         
    }   
    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
           
        ]);
    }
 
    /**
     * Display the specified resource.   
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,$id)
    {
       
        $user = auth()->user();
        $leaves_data =  LeaveManagement::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'Leaves detail data',
         'data'=>$leaves_data,
     ]);
    }
    public function update_data($data)
    {
        $leaves_data = LeaveManagement::where('id',$data['id'])->update([
           'start_date' => $data['start_date'],
           'end_date' => $data['end_date'],
           'leave_type' => $data['leave_type'],
           'reason' => $data['reason'],
        ]);
      
         return $leaves_data;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'leave_type' => 'required',
            'reason' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $leaves_data = $this->update_data($request->all());
        if ($leaves_data) {
            return response()->json([
                'result' => true,
                'message' => 'Leaves Updated successful.',
            ]); 
        } else {
            return $this->registrationFailed("updated failed");
        }
    }

    public function get_status(Holiday $holiday)
    {
        $user = auth()->user();
       
      $annual_leave =  LeaveManagement::where('leave_type',1)->where('user_id',$user->id)->count();
      $sick_leave =  LeaveManagement::where('leave_type',2)->where('user_id',$user->id)->count();
      $meternoty_leave =  LeaveManagement::where('leave_type',3)->where('user_id',$user->id)->count();
      $peternity_leave =  LeaveManagement::where('leave_type',4)->where('user_id',$user->id)->count();
        return response()->json([
            'result' => true,
            'message' => 'Leaves total',
            'data'=>[
                'annual_leave' => $annual_leave,
                'sick_leave' => $sick_leave,
                'meternoty_leave' => $meternoty_leave,
                'peternity_leave' => $peternity_leave,
            ],
        ]); 
    }
}
