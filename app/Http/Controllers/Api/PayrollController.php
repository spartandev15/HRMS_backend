<?php 
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\LeaveManagement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; 
use App\Models\Events;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
class PayrollController extends Controller
{
    /** 
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required',
            'amount' => 'required',
            'pay_date' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $payroll = $this->store($request->all());
        if ($payroll) {
            return response()->json([
                'result' => true,
                'message' => 'Pay ROll Created successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Pay ROll Created failed");
        }
    }  

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        $user = auth()->user();
        $payroll = Payrolls::create([
            'employee_id' => $data['employee_id'],
            'amount' => $data['amount'],
            'pay_date' => $data['pay_date'],
        ]);
       
         return $payroll;
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
        $projects =  Payrolls::where('id',$id)->where('employee_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'Pay ROll detail data',
         'data'=>$projects,
     ]);
    }
    public function update_data($data)
    {
        $payroll = Payrolls::where('id',$data['id'])->update([
            'employee_id' => $data['employee_id'],
            'amount' => $data['amount'],
            'pay_date' => $data['pay_date'],
        ]);
         return $payroll;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'descriptionn' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $payroll = $this->update_data($request->all());
        if ($payroll) {
            return response()->json([
                'result' => true,
                'message' => 'Pay ROll Updated Successful.',
                
            ]);
        } else {
            return $this->registrationFailed("Updated Failed");
        }
    }

}

 
