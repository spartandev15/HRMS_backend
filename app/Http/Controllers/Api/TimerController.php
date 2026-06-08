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
    // Below function is providing all the details from timers table
    public function get(Request $request){   
        $timer = Timer::all();
        if ($timer->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'No timer data found',
                'data' => [],
            ]);
        }
        return response()->json([
            'result' => true,
            'message' => 'Timer Data lists',
            'data'  => $timer,
        ]);
    }

    /* Below function is adding punch in for a user who is logged in based on token or Auth id and 
    getting his details for timer and users table*/
    public function punch_in(Request $request){
        $current_time_list = Carbon::now()->setTimezone('Asia/Kolkata');    
        // Check if the user has already punched in today and if the timer is running
        $data_check = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
            ->where('user_id', Auth::user()->id)
            ->where('status', 'running') // Make sure the status is 'running'
            ->first();
    
        if (!empty($data_check)) {
            // Timer already running, user cannot punch in again
            return response()->json([
                'result' => false,
                'message' => 'You have already punched in today.',
            ]);
        } else {
            // No timer running, allow punch-in
            $timer = Timer::create([
                'started_at' => $current_time_list,
                'user_id' => Auth::user()->id,
                'status' => 'running',
                'today_punchin' => $current_time_list,
            ]);
    
            // Fetch the newly created timer with user details
            $data = Timer::where('id', $timer->id)->with('user')->first();
    
            return response()->json([
                'result' => true,
                'message' => 'Timer created successfully',
                'data' => [
                    'timer' => $data,
                ]
            ]);
        }
    }
    
    /* Below function is adding punch out for a user who is logged in based on token or Auth id and 
    getting his details for timer and users table*/
        public function punch_out(Request $request){
            // Get the current date and time in 'Asia/Kolkata' timezone
            $current_time_list = Carbon::now()->setTimezone('Asia/Kolkata');
            // Fetch the timer data for the current user on the current date
            $data = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
                ->where('user_id', Auth::user()->id)
                ->where('status', 'running')  // Ensure status is 'running' (punched in)
                ->with('user')
                ->first();
        
            if (is_null($data)) {
                return response()->json([
                    'result' => false,
                    'message' => 'You have already punched out or no active timer found for today.',
                ]);
            }
        
            // Parse the started_at time and the current time
            $started_at = Carbon::parse($data->started_at);
            $end_time = $current_time_list;
        
            // Calculate the duration
            $hours = (int)$end_time->format('H') - (int)$started_at->format('H');
            $minutes = (int)$end_time->format('i') - (int)$started_at->format('i');
        
            if ($minutes < 0) {
                $minutes += 60;
                $hours--;
            }
        
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            $totalDuration = $hours . ':' . $minutes;
        
            // If there's an existing running_duration, add it to the new duration
            if (!is_null($data->running_duration)) {
                list($hours_back, $minutes_back) = explode(':', $data->running_duration);
        
                $hours_data = $hours + $hours_back;
                $minutes_data = $minutes + $minutes_back;
        
                if ($minutes_data >= 60) {
                    $minutes_data -= 60;
                    $hours_data += 1;
                }
        
                $total_data = $hours_data . ':' . $minutes_data;
            } else {
                $total_data = $totalDuration;
            }
        
            // Update the timer with the punch-out details
            Timer::where('id', $data->id)->update([
                'stopped_at' => $end_time,
                'status' => 'stop',
                'running_duration' => $total_data,
            ]);
        
            // Fetch the latest time update for response
            $latest_timeupdate = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
                ->with('user')
                ->first();
        
            return response()->json([
                'result' => true,
                'message' => 'Timer Punchout successfully',
                'data' => [
                    'timer' => $latest_timeupdate,
                ],
            ]);
        }
    
    //fetching all the data based on current date only
    public function get_detail(Request $request){
        $current_time_list = Carbon::now()->setTimezone('Asia/Kolkata');
        // Fetch all timers for the current date and current logged-in user's ID
        $data_check = Timer::whereDate('created_at', $current_time_list->format('Y-m-d'))
            ->where('user_id', Auth::user()->id)  // Check for the current logged-in user's ID
            ->with('user')
            ->get();  // Use get() to fetch all records for the user, not just the first one
        // Check if no timers are found for the current date and user
        if ($data_check->isEmpty()) {
            return response()->json([
                'result' => false,
                'message' => 'No data exists for the current user on this date',
                'data' => null
            ]);
        }

        // Iterate through each timer and calculate the duration
        foreach ($data_check as $timer) {
            $started_at = Carbon::parse($timer->started_at);           
            $end_time = $current_time_list;
            // Calculate the duration
            $hours = (int)$end_time->format('H') - (int)$started_at->format('H');
            $minutes = (int)$end_time->format('i') - (int)$started_at->format('i');
            if ($minutes < 0) {
                $minutes += 60;
                $hours--;
            }
            // Format hours and minutes with leading zeros
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
            $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
            $totalDuration = $hours . ':' . $minutes;
            // If the timer is still running, adjust the running duration
            if ($timer->status == 'running') {
                if (!is_null($timer->running_duration)) {
                    // If there's a previous running duration, add it to the current duration
                    list($hours_back, $minutes_back) = explode(':', $timer->running_duration);
                    $hours_data = $hours + $hours_back;
                    $minutes_data = $minutes + $minutes_back;
                    if ($minutes_data >= 60) {
                        $minutes_data -= 60;
                        $hours_data += 1;
                    }
                    // Update the running_duration field
                    $timer->running_duration = str_pad($hours_data, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes_data, 2, '0', STR_PAD_LEFT);
                } else {
                    // If no running duration, set the total duration as the running_duration
                    $timer->running_duration = $totalDuration;
                }
            }
        }
        // Return the data of all timers for the current day and logged-in user
        return response()->json([
            'result' => true,
            'message' => 'Timer detail lists',
            'data' => [
                'timers' => $data_check,  // Return all timers for the current user
            ]
        ]);
    }

    //  handling failed response 
    protected function registrationFailed($message){
        return response()->json([
            'result' => false,
            'message' => $message,   
        ]);
    }

    public function resumetime(Request $request){
        return response()->json([
            'result' => true,
            'message' =>'Getting time gap',
           'data' => $time_gap['time_gap'],

        ]);
    }   
}
