<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\User_Detail;
use Illuminate\Http\Request;
use App\Models\LeaveManagement;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Events;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
class DashboardController extends Controller
{                 
    public function index(Request $request){
        $user = auth()->user();
        $events = Events::where('user_id',$user->id)->get();
        $upcoming_events_count = Events::where('user_id',$user->id)->count();
        $pending_leave =  LeaveManagement::where('leave_type',5)->where('user_id',$user->id)->get();
        $pending_leave_count =  LeaveManagement::where('leave_type',5)->where('user_id',$user->id)->get();
        $leave_requests =  LeaveManagement::where('user_id',$user->id)->get();
        $leave_requests_count =  LeaveManagement::where('user_id',$user->id)->get();
        $whos_off_today = LeaveManagement::where('user_id', $user->id)
        ->where('start_date', Carbon::today()->toDateString())
        ->get(); 
        $whos_off_today_count = LeaveManagement::where('user_id', $user->id)
        ->where('start_date', Carbon::today()->toDateString())
        ->get();
        return response()->json([  
            'result' => true,
            'message' => 'Dashboard Detail',
            'data' => [
                'user' => $user,
                'upcoming_events_count' => $upcoming_events_count,
                'upcoming_events' => $events,
                'pending_leave' => $pending_leave,
                'pending_leave_count' => $pending_leave_count,
                'leave_requests_count' => $leave_requests_count,
                'leave_requests' => $leave_requests,
                'whos_off_today' => $whos_off_today,
                'whos_off_today_count' => $whos_off_today_count,
            ]
        ]);  
    }
}
 
