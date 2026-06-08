<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\Employee;
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
        $pending_leave =  LeaveManagement::where('status','pending')->where('user_id',$user->id)->with('employee_detail')->get();
        $pending_leave_count =  LeaveManagement::where('status','pending')->where('user_id',$user->id)->count();       
        $approved_leave =  LeaveManagement::where('status','approved')->where('user_id',$user->id)->with('employee_detail')->get();
        $approved_leave_count =  LeaveManagement::where('status','approved')->where('user_id',$user->id)->count();
        $rejected_leave =  LeaveManagement::where('status','rejected')->where('user_id',$user->id)->with('employee_detail')->get();
        $rejected_leave_count =  LeaveManagement::where('status','rejected')->where('user_id',$user->id)->count();       
        $leave_requests =  LeaveManagement::where('user_id',$user->id)->with('employee_detail')->get();
        $leave_requests_count =  LeaveManagement::where('user_id',$user->id)->count();
        return response()->json([  
            'result' => true,
            'message' => 'Dashboard Detail',
            'data' => [
                'user' => $user,
                'pending_leave' => $pending_leave,
                'pending_leave_count' => $pending_leave_count,
                'approved_leave' => $approved_leave,
                'approved_leave_count' => $approved_leave_count,
                'rejected_leave' => $rejected_leave,
                'rejected_leave_count' => $rejected_leave_count,
                'leave_requests_count' => $leave_requests_count,
                'leave_requests' => $leave_requests,
            ]
        ]);     
    }

    public function getUpcomingBirthdaysAndAnniversaries(){
        $currentDate = Carbon::today('Asia/Kolkata');
        $twoDaysLater = $currentDate->copy()->addDays(2);
        $currentDateFormatted = $currentDate->format('m-d');
        $twoDaysLaterFormatted = $twoDaysLater->format('m-d');
        
        // Fetch upcoming birthdays and anniversaries
        $upcomingBirthdays = Employee::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
            $currentDateFormatted,
            $twoDaysLaterFormatted,
        ])->get();
        
        $upcomingAnniversaries = Employee::whereRaw("DATE_FORMAT(joining_date, '%m-%d') BETWEEN ? AND ?", [
            $currentDateFormatted,
            $twoDaysLaterFormatted,
        ])->get();
        
        // If no events are found, return a message indicating that
        if ($upcomingBirthdays->isEmpty() && $upcomingAnniversaries->isEmpty()) {
            return [
                'message' => 'No upcoming birthdays or anniversaries within next 2 days.',
                'upcoming_birthdays' => [],
                'upcoming_anniversaries' => [],
                'upcoming_birthdays_count' => 0,
                'upcoming_anniversaries_count' => 0,
            ];
        }
    
        // Return the upcoming events data
        return [
            'message' => 'Upcoming birthdays and anniversaries in the next 2 days.',
            'upcoming_birthdays' => $upcomingBirthdays,
            'upcoming_anniversaries' => $upcomingAnniversaries,
            'upcoming_birthdays_count' => $upcomingBirthdays->count(),
            'upcoming_anniversaries_count' => $upcomingAnniversaries->count(),
        ];
    }
    
    
    
    
    public function hr_dashboard(Request $request){
        $user = auth()->user();
        $events = $this->getUpcomingBirthdaysAndAnniversaries();
        $pending_leave =  LeaveManagement::where('status','pending')->with('employee_detail')->get();
        $pending_leave_count =  LeaveManagement::where('status','pending')->count();
        $approved_leave =  LeaveManagement::where('status','approved')->with('employee_detail')->get();
        $approved_leave_count =  LeaveManagement::where('status','approved')->count();        
        $rejected_leave =  LeaveManagement::where('status','rejected')->with('employee_detail')->get();
        $rejected_leave_count =  LeaveManagement::where('status','rejected')->count();
        $leave_requests =  LeaveManagement::with('employee_detail')->get();
        $leave_requests_count =  LeaveManagement::count();
        $whos_off_today = LeaveManagement::whereDate('start_date', '<=', Carbon::today()->toDateString())
    ->whereDate('end_date', '>=', Carbon::today()->toDateString())
    ->where('status', 'approved')  // Ensure status is approved
    ->with('employee_detail')
    ->get();

    
    $whos_off_today_count = LeaveManagement::whereDate('start_date', '<=', Carbon::today()->toDateString())
    ->whereDate('end_date', '>=', Carbon::today()->toDateString())
    ->where('status', 'approved')  // Ensure status is approved
    ->count();

      
    
        return response()->json([  
            'result' => true,
            'message' => 'HR Dashboard Detail',
            'data' => [
                'user' => $user,
                'upcoming_events_count' => $events['upcoming_birthdays_count'] + $events['upcoming_anniversaries_count'],
                'upcoming_birthdays_count' => $events['upcoming_birthdays_count'],
                'upcoming_anniversaries_count' => $events['upcoming_anniversaries_count'],
                'upcoming_events' => $events,
                'pending_leave_count' => $pending_leave_count,
                'pending_leave' => $pending_leave,
                'approved_leave_count' => $approved_leave_count,
                'approved_leave' => $approved_leave,
                'rejected_leave_count' => $rejected_leave_count,
                'rejected_leave' => $rejected_leave,
                'leave_requests_count' => $leave_requests_count,
                'leave_requests' => $leave_requests,
                'whos_off_today_count' => $whos_off_today_count,
                'whos_off_today' => $whos_off_today,
                'my_time_off_count' => $approved_leave_count,
                'my_time_off' => $approved_leave,
            ]
        ]);
    }
    
}
 
