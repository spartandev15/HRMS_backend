<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\Project; 
use App\Models\TimerImage; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Timer;
use Carbon\Carbon;
class TimerController extends Controller
{
    public function get(Request $request){
        $timer = Timer::all();
        return response()->json([
            'result' => true,
            'message' => 'Timer Data lists',
            'data' => $timer,
        ]);
    }
    public function store(Request $request, int $id)
        {
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
            $timer = Timer::with(['project', 'user'])->mine()->where('status', 'running')->first() ?? [];
            
            if (!$timer) {
                return response()->json([
                    'result' => false,
                    'message' => 'No running timer found',
                    'data' => [],
                ]);
            }
        
            // Calculate overtime if the timer is running for longer than a standard period (e.g., 8 hours)
            $standardDuration = 8 * 60 * 60; // 8 hours in seconds
            $startedAt = \Carbon\Carbon::parse($timer->started_at, 'Asia/Kolkata');
            $currentDuration = now('Asia/Kolkata')->timestamp - $startedAt->timestamp;
        
            if ($currentDuration > $standardDuration) {
                $overtime = $currentDuration - $standardDuration;
                Timer::where('id', $id)->update([
                    'overtime' => gmdate('H:i:s', $overtime),
                ]);
            } 
        
            // Return the response as JSON
            return response()->json([
                'result' => true,
                'message' => 'Timer is Running',
                'data' => [
                    'project_name' => optional($timer->project)->name,
                    'started_at' => $timer->started_at,
                    'stopped_at' => '',
                    'user_name' => optional($timer->user)->name,
                    'email' => optional($timer->user)->email,
                    'overtime' => $timer->overtime,
                ],
            ]);
        }
        
        public function pause($id)
        {
            $timer = Timer::mine()->where('status', 'running')->find($id);

            if (!$timer) {
                return response()->json([
                    'result' => false,
                    'message' => 'No running timer found to pause',
                ]);
            }
               
            // Calculate the duration the timer has been running
            $startedAt = \Carbon\Carbon::parse($timer->started_at, 'Asia/Kolkata');
            $pausedAt = now('Asia/Kolkata');
            $runningDuration = $pausedAt->timestamp - $startedAt->timestamp;

            // Update the timer with the paused status and running duration
            $timer->update([
                'status' => 'paused',
                'paused_at' => $pausedAt,
                'running_duration' => $runningDuration,
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Timer has been paused',
                'data' => [
                    'id' => $timer->id,
                    'project_name' => optional($timer->project)->name,
                    'started_at' => $timer->started_at,
                    'paused_at' => $pausedAt,
                    'running_duration' => gmdate('H:i:s', $runningDuration),
                ],
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
                                                                         
        public function take_screeshot(Request $request){
                $photo = $request->file('screeshot_image');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photoPath = 'public/screeshot_image/' . $photoName;
        
                // Move the file to the public/profile_photos directory
                $photo->move(public_path('screeshot_image'), $photoName);
                $user_id = auth()->user()->id;
              
              TimerImage::create([
                'user_id' => $user_id,
                'project_id' => $request->project_id,
                'timer_id' => $request->timer_id,
                'image' => $photoPath,
              ]);
              return response()->json([
                'result' => true,
                'message' => 'Timer Image screenshot added',
             ]);
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
