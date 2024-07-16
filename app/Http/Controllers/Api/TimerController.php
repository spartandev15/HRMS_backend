<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Models\Project; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Timer;
use Carbon\Carbon;
class TimerController extends Controller
{
    public function store(Request $request, int $id)
        {
            // $data = $request->validate(['name' => 'required|between:3,100']);
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->registrationFailed($validator->errors()->all());
            }
            $timer = Project::mine()->findOrFail($id)
                                    ->timers()
                                    ->save(new Timer([
                                        'name' => $request->name,
                                        'user_id' => Auth::user()->id,
                                        'started_at' => new Carbon,
                                        'project_id' =>$id,
                                    ]));
            
             return response()->json([
                'result' => true,
                'message' => 'Timer created success',
                "data"=>[
                    'project_name' => $timer->name,
                    'started_at' => $timer->started_at,
                    'started_at' => $timer->started_at,
                    'user_name' => optional($timer->user)->name,
                    'email' => optional($timer->user)->email,
                ]
            ]);
        }

        public function running($id)
        {

             $timer = Timer::with('project')->mine()->running()->first() ?? [];
              dd($timer);  
             return response()->json([
                'result' => true,
                'message' => 'Get data Data',
                "data"=>[
                    'project_name' => $timer->name,
                    'started_at' => $timer->started_at,
                    'stopped_at' => '',
                    'user_name' => optional($timer->user)->name,
                    'email' => optional($timer->user)->email,
                ]
            ]);
        }
        // stop reunning
        public function stopRunning()
        {
            if ($timer = Timer::mine()->running()->first()) {
                $timer->update(['stopped_at' => new Carbon]);
            }

            return $timer;
        }


        // failed response 
        protected function registrationFailed($message)
        {
            return response()->json([
                'result' => false,
                'message' => $message,
               
            ]);
        }

}
