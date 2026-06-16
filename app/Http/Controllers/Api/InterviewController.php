<?php 

namespace App\Http\Controllers\Api;
use App\Models\Interview;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Mail;

class InterviewController extends Controller
{

    public function storeInterview(Request $request){
        $validator = Validator::make($request->all(), [
            'candidate_name'    => 'required|string|max:255',
            'interview_date'    => 'required|date|after_or_equal:today',
            'interview_time'    => 'required|date_format:H:i',
            'position'          => 'required|string|max:255',
            'email'             => 'required|email|max:255', // Candidate email
            'phone_number'      => 'required|string|max:20',
            'interview_type'    => 'required|in:In-person,Virtual,Phone',
            'interviewer_name'  => 'required|string|max:255',
            'interviewer_email' => 'required|string', // Multiple interviewers (comma-separated)
            'resume_file'       => 'required',
            'location'          => 'nullable|string|max:255',
            'timezone'          => 'nullable|string|max:50',
            'notes'             => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $interviewerEmails = explode(',', str_replace(' ', '', $request->input('interviewer_email')));
        $resumeFilePath = null;
        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');
            $folderPath = public_path('resumes');
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($folderPath, $filename);
            $resumeFilePath = url('public/resumes/' . $filename);
        }
        $interviewDate = Carbon::createFromFormat('d-m-Y', $request->input('interview_date'))->format('Y-m-d');
        $interviewTime = $request->input('interview_time');
        $interview = new Interview([
            'candidate_name'    => $request->input('candidate_name'),
            'email'             => $request->input('email'), // Candidate email
            'interviewer_email' => implode(',', $interviewerEmails),
            'phone_number'      => $request->input('phone_number'),
            'resume_file'       => $resumeFilePath,
            'position'          => $request->input('position'),
            'interview_type'    => $request->input('interview_type'),
            'interview_date'    => $interviewDate,
            'interview_time'    => $interviewTime,
            'interviewer_name'  => $request->input('interviewer_name'),
            'location'          => $request->input('location'),
            'timezone'          => $request->input('timezone'),
            'notes'             => $request->input('notes'),
            'consent_to_record' => $request->input('consent_to_record'),
            'orpect_user_id' => auth()->user()->orpect_user_id,
        ]);
        if ($interview->save()) {
            // Email template function
            $emailTemplate = function ($recipientName, $messageContent) use ($request, $interviewDate, $interviewTime) {
                return '
                    <html>
                        <head>
                            <title>Interview Scheduled</title>
                        </head>
                        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 10px;">
                            <div style="max-width: 600px; margin: auto; background: white; padding: 10px; border-radius: 8px; box-shadow: 0px 0px 10px #ccc;">
                                <h2 style="text-align: center; color: #333;">Interview Scheduled</h2>                        
                                <p>Dear ' . $recipientName . ',</p>
                                <p>' . $messageContent . '</p>
                                <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                                    <tr><td style="padding: 5px; background: #f0f0f0;"><strong>Candidate Name:</strong></td><td style="padding: 5px;">' . $request->input('candidate_name') . '</td></tr>
                                    <tr><td style="padding: 5px; background: #f0f0f0;"><strong>Position:</strong></td><td style="padding: 5px;">' . $request->input('position') . '</td></tr>
                                    <tr><td style="padding: 5px; background: #f0f0f0;"><strong>Date:</strong></td><td style="padding: 5px;">' . $interviewDate . '</td></tr>
                                    <tr><td style="padding: 5px; background: #f0f0f0;"><strong>Time:</strong></td><td style="padding: 5px;">' . $interviewTime . '</td></tr>
                                    <tr><td style="padding: 5px; background: #f0f0f0;"><strong>Interview Type:</strong></td><td style="padding: 5px;">' . $request->input('interview_type') . '</td></tr>
                                    <tr><td style="padding: 5px; background: #f0f0f0;"><strong>Location:</strong></td><td style="padding: 5px;">' . ($request->input('location') ?? 'Not Specified') . '</td></tr>
                                </table>
                                <p>Best Regards,</p>
                                <p><strong>HR Team</strong></p>
                            </div>
                        </body>
                    </html>
                ';
            };
            $interviewerMessage = "An interview has been scheduled for you with the following details. Please manage accordingly.";
            $candidateMessage = "Your interview has been scheduled on <strong>$interviewDate at $interviewTime</strong> in <strong>" . ($request->input('location') ?? 'Not Specified') . "</strong> via <strong>" . $request->input('interview_type') . "</strong>. Please inform us in advance if there are any changes to your plans.";
            foreach ($interviewerEmails as $email) {
                Mail::send([], [], function ($message) use ($email, $emailTemplate, $request, $interviewerMessage) {
                    $message->to($email)
                        ->subject('New Interview Scheduled')
                        ->html($emailTemplate($request->input('interviewer_name'), $interviewerMessage));
                });
            }
            Mail::send([], [], function ($message) use ($request, $emailTemplate, $candidateMessage) {
                $message->to($request->input('email'))
                    ->subject('Your Interview is Scheduled')
                    ->html($emailTemplate($request->input('candidate_name'), $candidateMessage));
            });
            return response()->json([
                'message' => 'Interview scheduled successfully and emails sent to interviewers & candidate.',
                'data'    => $interview
            ], 201);
        } else {
            return response()->json(['error' => 'Failed to schedule interview'], 500);
        }
    }

    
     



    // Get all scheduled interviews
    public function getAllInterviews()
    {
        $orpectUserId = auth()->user()->orpect_user_id;
        // $interviews = Interview::all();
        $interviews = Interview::where('orpect_user_id',$orpectUserId)->get();


        if ($interviews->isEmpty()) {
            return response()->json(['message' => 'No interviews found.'], 404);
        }

        return response()->json([
            'message' => 'All interviews retrieved successfully.',
            'data'    => $interviews
        ], 200);
    }

    // Get a specific interview by ID
    // public function getInterviewById($id)
    // {
    //     $interview = Interview::find($id);

    //     if (!$interview) {
    //         return response()->json(['message' => 'Interview not found.'], 404);
    //     }

    //     return response()->json([
    //         'message' => 'Interview retrieved successfully.',
    //         'data'    => $interview
    //     ], 200);
    // }

    // Update a specific interview
    public function updateInterview(Request $request)
    {
        // Find the interview
        $interview = Interview::find($request->id);
    
        if (!$interview) {
            return response()->json(['message' => 'Interview not found.'], 404);
        }
    
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'candidate_name'    => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'phone_number'      => 'required|string|max:20',
            'resume_file'       => 'nullable|max:2048',  // Allow nullable file
            'position'          => 'required|string|max:255',
            'interview_type'    => 'required|in:In-person,Virtual,Phone',
            'interview_date'    => 'required|date|after_or_equal:today',
            'interview_time'    => 'required|date_format:H:i',
            'interviewer_name'  => 'required|string|max:255',
            'location'          => 'nullable|string|max:255',
            'timezone'          => 'nullable|string|max:50',
            'notes'             => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
    
        // Handle file upload for resume (only if a new file is uploaded)
        $resumeFilePath = $interview->resume_file; // Keep the old resume path if no new file is uploaded
        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');
            
            // Define the folder path for resume storage
            $folderPath = public_path('resumes');
    
            // Ensure the folder exists, if not, create it
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }
    
            // Get the original file name
            $filename = $file->getClientOriginalName();
    
            // Append timestamp to make the filename unique
            $timestamp = Carbon::now()->format('YmdHis');
            $uniqueFilename = $timestamp . '_' . $filename;
    
            // Move the file to the desired folder
            $file->move($folderPath, $uniqueFilename);
    
            // Update resume file path with the new file URL
            $resumeFilePath = url('public/resumes/' . $uniqueFilename);
        }
    
        // Format the interview date and time
        $interviewDate = Carbon::createFromFormat('d-m-Y', $request->input('interview_date'))->format('Y-m-d');
        $interviewTime = $request->input('interview_time'); // already in H:i format
    
        // Update the interview details
        $interview->update([
            'candidate_name'    => $request->input('candidate_name'),
            'email'             => $request->input('email'),
            'phone_number'      => $request->input('phone_number'),
            'resume_file'       => $resumeFilePath,
            'position'          => $request->input('position'),
            'interview_type'    => $request->input('interview_type'),
            'interview_date'    => $interviewDate,
            'interview_time'    => $interviewTime,
            'interviewer_name'  => $request->input('interviewer_name'),
            'location'          => $request->input('location'),
            'timezone'          => $request->input('timezone'),
            'notes'             => $request->input('notes'),
            'consent_to_record' => $request->input('consent_to_record'),
        ]);
    
        return response()->json([
            'message' => 'Interview updated successfully.',
            'data'    => $interview
        ], 200);
    }
    

    // Delete a specific interview
    public function deleteInterview(Request $request)
    {
        // Find the interview
        // echo "<pre>";print_r($request->all());die;
        $interview = Interview::find($request->id);

        if (!$interview) {
            return response()->json(['message' => 'Interview not found.'], 404);
        }

        // Delete the interview record
        $interview->delete();

        return response()->json(['message' => 'Interview deleted successfully.'], 200);
    }

    public function storeVacancy(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'job_title'           => 'required|string|max:255',
            'location'            => 'required|string|max:255',
            'skills_required'     => 'required|string',
            'salary_range'        => 'required|in:10-20,20-30,30-40,40-50,50-60',
            'job_type'            => 'required|in:Work from Home,Office,Hybrid',
            'company_information' => 'required|string',
            'job_responsibilities'=> 'required|string',
            'contact_email'       => 'required|email|max:255',
            'experience'          => 'required|in:0-1,1-2,2-3,3-5,5-7,7-10',
            'joining_time'        => 'required|in:Immediate,15 Days,30 Days,45 Days,60 Days',
        ]);

        // If validation fails, return error response with details
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        // Create a new vacancy record
        $vacancy = Vacancy::create([
            'job_title'           => $request->input('job_title'),
            'location'            => $request->input('location'),
            'skills_required'     => $request->input('skills_required'),
            'salary_range'        => $request->input('salary_range'),
            'job_type'            => $request->input('job_type'),
            'company_information' => $request->input('company_information'),
            'job_responsibilities'=> $request->input('job_responsibilities'),
            'contact_email'       => $request->input('contact_email'),
            'experience'          => $request->input('experience'),
            'joining_time'        => $request->input('joining_time'),
            'status'              => $request->input('status'),
        ]);

        return response()->json([
            'message' => 'Vacancy created successfully.',
            'data'    => $vacancy
        ], 201);
    }

    // Get all vacancies
    public function getAllVacancies()
    {
        $vacancies = Vacancy::all();

        if ($vacancies->isEmpty()) {
            return response()->json(['message' => 'No vacancies found.'], 404);
        }

        return response()->json([
            'message' => 'All vacancies retrieved successfully.',
            'data'    => $vacancies
        ], 200);
    }

    // Get a specific vacancy by ID
    public function getVacancyById(Request $request)
    {
        $vacancy = Vacancy::find($request->id);

        if (!$vacancy) {
            return response()->json(['message' => 'Vacancy not found.'], 404);
        }

        return response()->json([
            'message' => 'Vacancy retrieved successfully.',
            'data'    => $vacancy
        ], 200);
    }

    // Update a specific vacancy
    public function updateVacancy(Request $request)
    {
       
        // Find the vacancy
        $vacancy = Vacancy::find($request->id);

        if (!$vacancy) {
            return response()->json(['message' => 'Vacancy not found.'], 404);
        }

        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'job_title'           => 'required|string|max:255',
            'location'            => 'required|string|max:255',
            'skills_required'     => 'required|string',
            'salary_range'        => 'required|in:10-20,20-30,30-40,40-50,50-60',
            'job_type'            => 'required|in:Work from Home,Office,Hybrid',
            'company_information' => 'required|string',
            'job_responsibilities'=> 'required|string',
            'contact_email'       => 'required|email|max:255',
            'experience'          => 'required|in:0-1,1-2,2-3,3-5,5-7,7-10',
            'joining_time'        => 'required|in:Immediate,15 Days,30 Days,45 Days,60 Days',
            'status'              => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        // Update the vacancy details
        $vacancy->update([
            'job_title'           => $request->input('job_title'),
            'location'            => $request->input('location'),
            'skills_required'     => $request->input('skills_required'),
            'salary_range'        => $request->input('salary_range'),
            'job_type'            => $request->input('job_type'),
            'company_information' => $request->input('company_information'),
            'job_responsibilities'=> $request->input('job_responsibilities'),
            'contact_email'       => $request->input('contact_email'),
            'experience'          => $request->input('experience'),
            'joining_time'        => $request->input('joining_time'),
            'status'              => $request->input('status'),
        ]);

        return response()->json([
            'message' => 'Vacancy updated successfully.',
            'data'    => $vacancy
        ], 200);
    }

    // Delete a specific vacancy
    public function deleteVacancy(Request $request)
    {
        // echo "<pre>";print_r($request->id);die;
        $vacancy = Vacancy::find($request->id);

        if (!$vacancy) {
            return response()->json(['message' => 'Vacancy not found.'], 404);
        }

        // Delete the vacancy record
        $vacancy->delete();

        return response()->json(['message' => 'Vacancy deleted successfully.'], 200);
    }
}