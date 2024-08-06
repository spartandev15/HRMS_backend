<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\Events;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
       $events =  Events::where('user_id',$user->id)->get();
       return response()->json([
        'result'  => true,
        'message' => 'Events Created successful.',
        'data'=>$events,
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
        $events = $this->store($request->all());
        if ($events) {
            return response()->json([
                'result' => true,
                'message' => 'Events Created successful.',
                8
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
        $events_data = Events::create([
            'description' => $data['description'],
                'title' => $data['title'],
                'members' => $data['members'],
                'user_id' => $user->id,
        ]);
         return $events_data;
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
        $events_data =  Events::where('id',$id)->where('user_id',$user->id)->get();
        return response()->json([
         'result' => true,
         'message' => 'Events detail data',
         'data'=>$events_data,
     ]);
    }
    public function update_data($data)
    {
        $events_data = Events::where('id',$data['id'])->update([
            'description' => $data['description'],
                'title' => $data['title'],
                 'members' => $data['members'],
                ]);
         return $events_data;
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
        $events_data = $this->update_data($request->all());
        if ($events_data) {
            return response()->json([
                'result' => true,
                'message' => 'Events updated successful.',
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
