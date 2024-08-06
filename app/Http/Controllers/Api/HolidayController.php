<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\Holiday;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class HolidayController extends Controller
{
    /**
 
    * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
       $holidays =  Holiday::where('user_id',$user->id)->get();
       return response()->json([
        'result' => true,
        'message' => 'Holiday Created successful.',
        'data'=>$holidays,
      ]);
    }
   
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $holidays = $this->store($request->all());
        if ($holidays) {
            return response()->json([
                'result' => true,
                'message' => 'Holiday Created successful.',
                
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
        $holiday_data = Holiday::create([
            'description' => $data['description'],
                'title' => $data['title'],
                'date' => $data['date'],
                'user_id' => $user->id,
        ]);
         return $holiday_data;
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
        $holidays =  Holiday::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'Holiday detail data',
         'data'=>$holidays,
     ]);
    }
    public function update_data($data)
    {
        $holiday_data = Holiday::where('id',$data['id'])->update([
            'description' => $data['description'],
                'title' => $data['title'],
                'date' => $data['date']
        ]);
         return $holiday_data;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }
        $holidays = $this->update_data($request->all());
        if ($holidays) {
            return response()->json([
                'result' => true,
                'message' => 'Holiday updated successful.',
                
            ]);
        } else {
            return $this->registrationFailed("updated failed");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        //
    }
}
