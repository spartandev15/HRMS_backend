<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\LeaveManagement;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LeaveManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */  
    // public function index(Request $request)
    // {
    //     // Fetch all leaves for all users (HR view)
    //     $leaves = LeaveManagement::with('employee')  // Eager load employee data if needed
    //                     ->orderBy('created_at', 'desc') // Order by creation date, newest first
    //                     ->get();
    
    //     // If no leaves found, return a message
    //     if ($leaves->isEmpty()) {
    //         return response()->json([
    //             'result' => false,
    //             'message' => 'No leave records found.',
    //         ]);
    //     }
    
    //     // Decode the 'leave_data' JSON field for each leave record
    //     $leaves->each(function ($leave) {
    //         // Decode 'leave_data' if it's a valid JSON string
    //         if ($leave->leave_data) {
    //             $leave->leave_data = json_decode($leave->leave_data, true);  // Decode as associative array
    //         }
    //     });
    
    //     // Optionally, group the leaves by 'created_at' date (yyyy-mm-dd)
    //     $grouped = $leaves->groupBy(function ($leave) {
    //         return \Carbon\Carbon::parse($leave->created_at)->toDateString();
    //     });
    
    //     // Return the grouped or sorted leaves with decoded 'leave_data'
    //     return response()->json([
    //         'result' => true,
    //         'message' => 'Leave data fetched successfully.',
    //         'data' => $grouped,  // Return grouped data if needed or directly return $leaves for flat list
    //     ]);
    // }
    
    
    public function getEmployeesLeaves(Request $request)
    {
        // Define the number of records per page (you can adjust this value)
        $perPage = $request->input('per_page', 15);  // Default to 15 items per page
        $page = $request->input('page', 1);

        $orpectUserId = auth()->user()->orpect_user_id;

        $leaves = Leave::whereHas('user', function ($q) use ($orpectUserId) {
                    $q->where('orpect_user_id', $orpectUserId);
                })
                ->paginate($perPage, ['*'], 'page', $page);

        // Paginate the leaves
        // $leaves = Leave::paginate($perPage, ['*'], 'page', $page);
    
        // Decode the leave_data for each leave record
        $leaves->getCollection()->transform(function ($leave) {
            $leave->leave_data = json_decode($leave->leave_data, true); // Decoding to an array
            return $leave;
        });
    
        return response()->json([
            'result' => true,  
            'message' => 'Get Leaves Data',
            'leaves' => $leaves,
            'pagination' => [
                'total' => $leaves->total(),
                'current_page' => $leaves->currentPage(),
                'per_page' => $leaves->perPage(),
                'last_page' => $leaves->lastPage(),
                'from' => $leaves->firstItem(),
                'to' => $leaves->lastItem(),
            ]
        ]);
    }
     
    public function index(Request $request)
    {
        // Define the number of results per page
        $perPage = $request->input('per_page', 10);  // Adjust this value based on your requirements
    
        // Fetch leaves for the current page with pagination
        $leaves = LeaveManagement::with('employee')  // Eager load employee data if needed
            ->orderBy('created_at', 'desc')  // Order by creation date, newest first
            ->paginate($perPage);  // Paginate results instead of using skip and take
    
        // If no leaves found, return a message
        if ($leaves->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'No leave records found.',
            ]);
        }
    
        // Decode the 'leave_data' JSON field for each leave record
        $leaves->getCollection()->transform(function ($leave) {
            // Decode 'leave_data' if it's a valid JSON string
            if ($leave->leave_data) {
                $leave->leave_data = json_decode($leave->leave_data, true);  // Decode as associative array
            }
            return $leave;
        });
    
        // Optionally, group the leaves by 'created_at' date (yyyy-mm-dd)
        $grouped = $leaves->getCollection()->groupBy(function ($leave) {
            return \Carbon\Carbon::parse($leave->created_at)->toDateString();
        });
    
        // Return the paginated and grouped leaves with decoded 'leave_data'
        return response()->json([
            'result' => true,
            'message' => 'Leave data fetched successfully.',
            'data' => $grouped,  // Return grouped data
            'pagination' => [
                'total' => $leaves->total(),
                'current_page' => $leaves->currentPage(),
                'per_page' => $leaves->perPage(),
                'last_page' => $leaves->lastPage(),
                'from' => $leaves->firstItem(),
                'to' => $leaves->lastItem(),
            ]
        ]);
    }
    
   
    public function all_leaves(Request $request)
    {
        $user = auth()->user();
      $status =  $request->status;
      $leaves = LeaveManagement::query();

      if (!empty($status)) {
          $leaves->where('status', $status);
      }
       
      $leaves = $leaves->with('employee_detail')->get();
       return response()->json([
        'result' => true,
        'message' => 'Get Leave Data successful.',
        'data'=>$leaves,
       ]);
    }
   
   /**
     * Show the form for creating a new resource.
     */

     public function create(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'start_date' => 'required|date',
             'end_date' => 'required|date|after_or_equal:start_date',
             'leave_type' => 'required|in:paid_leaves,sick_leaves,unpaid_leaves',
             'reason' => 'required|string',
         ]);
     
         if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 400);
         }
     
         $user_id = auth()->user()->id;
         
         // Check if leave is already applied for any of the selected dates
         $existingLeave = LeaveManagement::where('user_id', $user_id)
             ->where(function ($query) use ($request) {
                 $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                     ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                     ->orWhereRaw('? BETWEEN start_date AND end_date', [$request->start_date])
                     ->orWhereRaw('? BETWEEN start_date AND end_date', [$request->end_date]);
             })
             ->exists();
     
         if ($existingLeave) {
             return response()->json(['error' => 'Leave already applied for selected dates'], 400);
         }
     
         // Calculate the number of leave days correctly
         $startDate = Carbon::parse($request->start_date);
         $endDate = Carbon::parse($request->end_date);
         $daysRequested = $startDate->diffInDays($endDate) + 1; // Include both start & end dates
     
         // Store the leave request first
         $leaveRequest = $this->store($request->all());
     
         // Fetch leave data
         $leave = Leave::where('user_id', $user_id)->first();
         if (!$leave) {
             return response()->json(['error' => 'Leave record not found'], 404);
         }
     
         $leaveData = json_decode($leave->leave_data, true);
         $leaveType = $request->leave_type;
     
         if (!isset($leaveData[$leaveType])) {
             return response()->json(['error' => 'Invalid leave type'], 400);
         }
     
         // **Modify this part: Deduct leave only if status is "approved"**
         if ($leaveRequest->status == 'approved') {
             if ($leaveData[$leaveType]['Pending'] < $daysRequested) {
                 return response()->json(['error' => 'Insufficient leave balance for this type! Try changing the type of the leave'], 400);
             }
     
             // Deduct leaves
             $leaveData[$leaveType]['Taken'] += $daysRequested;
             $leaveData[$leaveType]['Pending'] -= $daysRequested;
     
             // Update overall leave data
             $leave->taken += $daysRequested;
             $leave->pending -= $daysRequested;
             $leave->leave_data = json_encode($leaveData);
             $leave->save();
         }
     
         return response()->json([
             'result' => true,
             'message' => 'Leave applied successfully. Leave balance will be updated upon approval.',
             'updated_leave_data' => $leaveData,
             'days_requested' => $daysRequested, // Debugging info
         ]);
     }
     
    


    /**
     * Store a newly created resource in storage.
     */   
    public function store($data)
    {
        $user = auth()->user();
        $user_id = auth()->user()->id;
        $leaves_data = LeaveManagement::create([
                 'user_id' => $user_id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'leave_type' => $data['leave_type'],
                'reason' => $data['reason'],
                'status' => 'pending'
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
        $leaves_data =  LeaveManagement::where('id',$id)->get();
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
           'status' => $data['status']
        ]);
        return $leaves_data;
    }
    public function update_leave_status($data)
    {
       // echo "Changing leave status";die;
        $leaves_data = LeaveManagement::where('id',$data['id'])->update([
            'status' => $data['status']
        ]);
        return $leaves_data;
    }
    
    /**
     * Update the specified resource in storage.
     */

     public function update(Request $request)
     {
         $id = $request->id;
         $validator = Validator::make($request->all(), [
             'first_name' => 'required|string|max:255',
             'id' => 'required',
             'email' => 'required|email',
         ]);
     
         if ($validator->fails()) {
             return $this->registrationFailed($validator->errors()->all());
         }
     
         $employee_data = Employee::where('id', $id)->first();
         if (!$employee_data) {
             return response()->json([
                 'result' => false,
                 'message' => 'Employee not found',
             ]);
         }
     
         $user = User::where('id', $employee_data->user_id)->update([
             'name' => $request->first_name,
             'last_name' => $request->last_name,
             'email' => $request->email,
             'designation' => $request->designation,
             'employee_id' => $request->employee_id,
         ]);
     
         Employee::where('id', $id)->update([
             'first_name' => $request->first_name,
             'last_name' => $request->last_name,
             'date_of_birth' => $request->date_of_birth,
             'designation' => $request->designation,
             'employee_id' => $request->employee_id,
             'email' => $request->email,
             'joining_date' => $request->joining_date,
             'phone' => $request->phone,
         ]);
     
         User_Detail::where('user_id', $employee_data->user_id)->update([
             'phone' => $request->phone,
             'joining_date' => $request->joining_date,
         ]);
     
         $leave = Leave::where('user_id', $employee_data->user_id)->first();
         $remaining_leaves = null; 
     
         if ($leave && $request->has('leave_type') && $request->has('days')) {
             $leaveData = json_decode($leave->leave_data, true);
     
             if (isset($leaveData[$request->leave_type]) && $leaveData[$request->leave_type]['Pending'] >= $request->days) {
                 $leaveData[$request->leave_type]['Taken'] += $request->days;
                 $leaveData[$request->leave_type]['Pending'] -= $request->days;
                 
                 $leave->leave_data = json_encode($leaveData);
                 $leave->taken += $request->days;
                 $leave->pending -= $request->days;
                 $leave->save();
     
                 $remaining_leaves = $leaveData[$request->leave_type]['Pending']; // Set remaining leave count
             } else {
                 return response()->json([
                     'result' => false,
                     'message' => 'Insufficient leave balance for this type! Try changing the type of the leave',
                 ]);
             }
         }
     
         return response()->json([
             'result' => true,
             'message' => 'Employee Data Updated',
             'remaining_leaves' => $remaining_leaves, // Return remaining leaves after update
         ]);
     }
     

    public function change_leave_status(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:approved,rejected,pending,disapproved',
        'id' => 'required|exists:leave_managements,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $leaveRequest = LeaveManagement::find($request->id);
    
    if (!$leaveRequest) {
        return response()->json(['error' => 'Leave request not found'], 404);
    }

    $previousStatus = $leaveRequest->status;
    $leaveRequest->status = $request->status;
    $leaveRequest->save();

    // Only proceed if status is changed to "approved" AND was not already approved
    if ($previousStatus != 'approved' && $request->status == 'approved') {
        $user_id = $leaveRequest->user_id;
        $leave = Leave::where('user_id', $user_id)->first();

        if (!$leave) {
            return response()->json(['error' => 'Leave record not found'], 404);
        }

        $leaveData = json_decode($leave->leave_data, true);
        $leaveType = $leaveRequest->leave_type;

        if (!isset($leaveData[$leaveType])) {
            return response()->json(['error' => 'Invalid leave type'], 400);
        }

        // Calculate number of leave days
        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1; // Include both start & end dates

        // Debugging: Log current leave balance
        \Log::info("User ID: $user_id | Type: $leaveType | Pending Before: " . $leaveData[$leaveType]['Pending']);

        // Check if enough leave balance is available before deducting
        if ($leaveData[$leaveType]['Pending'] < $daysRequested) {
            return response()->json(['error' => 'Insufficient leave balance for this type'], 400);
        }

        // Deduct leaves
        $leaveData[$leaveType]['Taken'] += $daysRequested;
        $leaveData[$leaveType]['Pending'] -= $daysRequested;

        // Update overall leave data
        $leave->taken += $daysRequested;
        $leave->pending -= $daysRequested;
        $leave->leave_data = json_encode($leaveData);

        // Debugging: Log updated leave balance
        \Log::info("Updated Pending: " . $leaveData[$leaveType]['Pending']);
        
        $leave->save();
    }

    return response()->json([
        'result' => true,
        'message' => 'Leave status updated successfully.',
    ]);
}

     
    public function get_status(Holiday $holiday)
    {
        $user = auth()->user();
       
      $annual_leave =  LeaveManagement::where('leave_type',1)->where('user_id',$user->id)->count();
      $sick_leave =  LeaveManagement::where('leave_type',2)->where('user_id',$user->id)->count();
      $meternoty_leave =  LeaveManagement::where('leave_type',3)->where('user_id',$user->id)->count();
      $peternity_leave =  LeaveManagement::where('leave_type',4)->where('user_id',$user->id)->count();
      $pending_leave =  LeaveManagement::where('leave_type',5)->where('user_id',$user->id)->count();
        return response()->json([
            'result' => true,
            'message' => 'Leaves total',
            'data'=>[
                'annual_leave' => $annual_leave,
                'sick_leave' => $sick_leave,
                'meternoty_leave' => $meternoty_leave,
                'peternity_leave' => $peternity_leave,
                'pending_leave' => $pending_leave,
            ],
        ]); 
    }
 
    
    
    
    
   
    public function getUserLeaves($id){
        $user = User::find($id); // Assuming you have a User model
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found',
            ]);
        }
        $userLeaves = Leave::where('user_id', $id)->get();
        if ($userLeaves->isNotEmpty()) {
            $user->leaves = $userLeaves->first(); // We take the first (or only) leave record
            $user->leaves->leave_data = json_decode($user->leaves->leave_data, true);
        } else {
            $user->leaves = (object) [];
        }
        return response()->json([
            'result' => true,
            'message' => 'Get User with Specific Leaves Data',
            'user' => $user
        ]);
    }

    public function getEmployeeLeaves($id){
        $user = User::find($id); // Assuming you have a User model
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found',
            ]);
        }
        $userLeaves = LeaveManagement::where('user_id', $id)->get();
        if ($userLeaves->isNotEmpty()) {
            foreach ($userLeaves as $leave) {
                $leave->leave_data = json_decode($leave->leave_data, true);
            }
        } else {
            $userLeaves = []; // No leave records found
        }
        $user->leaves = $userLeaves;
        return response()->json([
            'result' => true,
            'message' => 'User leave details retrieved successfully!',
            'user' => $user
        ]);
    }

}
