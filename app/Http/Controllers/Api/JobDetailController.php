<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Models\JobDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Auth;    
class JobDetailController extends Controller
{
   
    /**
     * Store a newly created resource in storage.
     */
    public function job_store(Request $request)
    {
        $user = auth()->user();

        $jobDetailData = JobDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'job_title' => $request->job_title,
                'job_category' => $request->job_category,
                'line_member' => $request->line_member,
                'join_date' => $request->join_date,
                'employment_status' => $request->employment_status,
                'user_id' => $user->id,
            ]
        );
        
        return response()->json([
            'result' => true,
            'message' => 'Job detail updated successfully.',
        ]);
    }
    public function education_details(Request $request)
    {
        $user = auth()->user();

        $jobDetailData = JobDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'education_level' => $request->education_level,
                'education_institute' => $request->education_institute,
                'education_year' => $request->education_year,
                'education_score' => $request->education_score,
                'user_id' => $user->id,
            ]
        );
        
        return response()->json([
            'result' => true,
            'message' => 'Education detail updated successfully.',
        ]);
        
    }
    public function work_experience(Request $request)
    {
        $user = auth()->user();

        $jobDetailData = JobDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'work_experience_company' => $request->work_experience_company,
                'work_experience_job_title' => $request->work_experience_job_title,
                'work_experience_from' => $request->work_experience_from,
                'work_experience_to' => $request->work_experience_to,
                'user_id' => $user->id,
            ]
        );
        
        return response()->json([
            'result' => true,
            'message' => 'Work Experience updated successfully.',
        ]);
        
    }
    public function salary_detail(Request $request)
    {
        $user = auth()->user();
        $JobDetail_data = JobDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'salary_component' => $request->salary_component,
                'salary_pay_frequency' => $request->salary_pay_frequency,
                'salary_currency' => $request->salary_currency,
                'salary_amount' => $request->salary_amount,
                'salary_account_number' => $request->salary_account_number,
                'salary_account_type' => $request->salary_account_type,
                'salary_bank_name' => $request->salary_bank_name,
                'salary_ifsc_code' => $request->salary_ifsc_code,
                'user_id' => $user->id,
            ]
        );
        
        return response()->json([
            'result' => true,
            'message' => 'Sallery Detail Updated successful.',
            
        ]);
    }
    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
           
        ]); 
    }
 }
