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
            'data'  => $timer,
        ]);
    }
    public function get_detail(Request $request){
        $current_time_list = Carbon::now()->setTimezone('Asia/Kolkata');
        // Fetch the timer data
        $data_check = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
            ->with('user')
            ->first();
            
           
            if(!empty($data_check)){

            // Parse the started_at time
            $started_at = Carbon::parse($data_check->started_at);
            
            $end_time = $current_time_list;
            // Calculate the duration
            if ((int)$end_time->format('H') >= (int)$started_at->format('H')) {
                $hours = (int)$end_time->format('H') - (int)$started_at->format('H');
            
            } else {
                // Handle cases where the end time is on the next day (e.g., started_at is 23:00, end_time is 01:00)
                $hours = ((int)$end_time->format('H')) - (int)$started_at->format('H');
            }

            if ((int)$end_time->format('i') >= (int)$started_at->format('i')) {
                $minutes = (int)$end_time->format('i') - (int)$started_at->format('i');
            } else {
                $minutes = ((int)$started_at->format('i')) - (int)$end_time->format('i');
                // $hours--;  // Borrow an hour because we've added 60 minutes
            }
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            // $totalDuration = sprintf('%d:%d',$hours, $minutes);
            $totalDuration = $hours.':'.$minutes;
         
            if(!is_null($data_check->running_duration)){
                list($hours_back, $minutes_back) = explode(':', $data_check->running_duration);
               
                $hours_data = $hours + $hours_back;
                $minutes_data = $minutes + $minutes_back;
                if ($minutes_data >= 60) {
                    $minutes_data -= 60;
                    $hours_data += 1;
                }
                $hours_check_ = str_pad($hours_data, 2, '0', STR_PAD_LEFT);
                $minutes_check_ = str_pad($minutes_data, 2, '0', STR_PAD_LEFT);
                // dd($minutes_back);
                // You can now use $hours and $minutes as needed
                // dd($hours_back, $minutes_back,$hours,$minutes);  // This will dump the values of hours and minute
                $data_check->running_duration = $hours_check_.':'.$minutes_check_;
            }
                return response()->json([
                    'result' => true,
                    'message' => 'Timer detial  lists',
                    "data"=>[
                        'timer' => $data_check,
                    ]
                ]);
            }else{
                return $this->registrationFailed('Timer is not valid');
            }
    }
        public function punch_in(Request $request)
        {
            $current_time_list = Carbon::now()->setTimezone('Asia/Kolkata');
          
            // Fetch the timer data
            $data_check = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
                ->with('user')
                ->first();
                if(!empty($data_check)){
                    $timer = Timer::where('id',$data_check->id)->update(['started_at' => $current_time_list,'stopped_at'=> '','user_id' => Auth::user()->id,'status' =>  'running',]);
                    $data = Timer::where('id',$data_check->id)->with('user')->first();
                    return response()->json([
                        'result' => true,
                        'message' => 'Timer created successfully',
                        "data"=>[
                            'timer' => $data,
                        ]
                    ]);
                }else{
                   $timer = Timer::create(['started_at' => $current_time_list,'user_id' => Auth::user()->id,'status' =>  'running',]);
                    $data = Timer::where('id',$timer->id)->with('user')->first();
                    return response()->json([
                        'result' => true,
                        'message' => 'Timer created successfully',
                        "data"=>[
                            'timer' => $data,
                        ]
                    ]);
                }
        }
        public function punch_out(Request $request)
        {
            // Get the current date and time in 'Asia/Kolkata' timezone
            $current_time_list = Carbon::now()->setTimezone('Asia/Kolkata');

            // Fetch the timer data
            $data = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
                ->with('user')
                ->first(); 

            if (is_null($data)) {
                return $this->registrationFailed('Timer is not valid');
            }

            // Parse the started_at time
            $started_at = Carbon::parse($data->started_at);
            
            $end_time = $current_time_list;
            
            
            // Calculate the duration
            if ((int)$end_time->format('H') >= (int)$started_at->format('H')) {
                $hours = (int)$end_time->format('H') - (int)$started_at->format('H');
               
            } else {
                // Handle cases where the end time is on the next day (e.g., started_at is 23:00, end_time is 01:00)
                $hours = ((int)$end_time->format('H')) - (int)$started_at->format('H');
            }
           
            if ((int)$end_time->format('i') >= (int)$started_at->format('i')) {
                $minutes = (int)$end_time->format('i') - (int)$started_at->format('i');
            } else {
                $minutes = ((int)$started_at->format('i')) - (int)$end_time->format('i');
                // $hours--;  // Borrow an hour because we've added 60 minutes
            }
          
            // dd($started_at->format('i'));
            // dump('Started At: ', $started_at->format('H:i'));
            // dump('End Time: ', $end_time->format('H:i'));
            // dump('Duration: ', "{$hours} hours");
            // dump('Duration: ', "{$minutes} Minutes");
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            // $totalDuration = sprintf('%d:%d',$hours, $minutes);
            $totalDuration = $hours.':'.$minutes;
             //    1 31  
            if(!is_null($data->running_duration)){
                list($hours_back, $minutes_back) = explode(':', $data->running_duration);
               
                $hours_data = $hours + $hours_back;
                $minutes_data = $minutes + $minutes_back;
                if ($minutes_data >= 60) {
                    $minutes_data -= 60;
                    $hours_data += 1;
                }
                // dd($minutes_back);
                // You can now use $hours and $minutes as needed
                // dd($hours_back, $minutes_back,$hours,$minutes);  // This will dump the values of hours and minute
                $total_data = $hours_data.':'.$minutes_data;
            }else{
                $total_data = $totalDuration;
            }
            Timer::where('id',$data->id)->update([
                'stopped_at' =>  $end_time,
                'status' =>  'stop',
                'running_duration' =>  $total_data,
            ]);
            $latest_timeupdate = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
                ->with('user')
                ->first(); 

            return response()->json([
                'result' => true,
                'message' => 'Timer Punchout successfully',
                "data"=>[
                    'timer' => $latest_timeupdate,
                ]
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
