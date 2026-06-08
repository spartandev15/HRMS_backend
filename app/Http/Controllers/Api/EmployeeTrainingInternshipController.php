<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\EmployeeTrainingInternship;


class EmployeeTrainingInternshipController extends Controller
{

    public function index()
    {
        $data = EmployeeTrainingInternship::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        //   dd($request->all());
        $validator = Validator::make($request->all(), [

            'first_name' => 'required',
            'email' => 'required|email',

        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $document = null;

        if ($request->hasFile('document_file')) {

            $file = $request->file('document_file');
            $document = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('documents'), $document);
        }

        $data = EmployeeTrainingInternship::create([

            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'user_id' => $request->user_id,
            
            'joining_date' => $request->joining_date,
            'expected_end_date' => $request->expected_end_date,
            'department' => $request->department,
            'mentor' => $request->mentor,
            // trainee 
            'work_location' => $request->work_location,
            'training_program_name' => $request->training_program_name,
            'training_type' => $request->training_type,
            'duration_in_months' => $request->duration_in_months,
            'skills_to_learn' => $request->skills_to_learn,
            'has_prior_experience' => $request->has_prior_experience,
            // internship
            'work_mode' => $request->work_mode,
            'university_name' => $request->university_name,
            'college_name' => $request->college_name,
            'course_name' => $request->course_name,
            'branch' => $request->branch,
            'current_year' => $request->current_year,
            'internship_type' => $request->internship_type,
            'stipend_amount' => $request->stipend_amount,
            'document_file' => $document,
            'status' => $request->status ?? 1, // 
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data added successfully',
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $data = EmployeeTrainingInternship::find($id);

        if (!$data) {
                                                                                                   
            return response()->json([
                'status' => false,
                'message' => 'Record not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {

        $data = EmployeeTrainingInternship::find($id);

        if (!$data) {

            return response()->json([
                'status' => false,
                'message' => 'Record not found'
            ]);
        }

        $document = $data->document_file;

        if ($request->hasFile('document_file')) {

            $file = $request->file('document_file');
            $document = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('documents'), $document);
        }

        $data->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'user_id' => $request->user_id,
            
            'joining_date' => $request->joining_date,
            'expected_end_date' => $request->expected_end_date,
            'department' => $request->department,
            'mentor' => $request->mentor,
            // trainee 
            'work_location' => $request->work_location,
            'training_program_name' => $request->training_program_name,
            'training_type' => $request->training_type,
            'duration_in_months' => $request->duration_in_months,
            'skills_to_learn' => $request->skills_to_learn,
            'has_prior_experience' => $request->has_prior_experience,
            // internship
            'work_mode' => $request->work_mode,
            'university_name' => $request->university_name,
            'college_name' => $request->college_name,
            'course_name' => $request->course_name,
            'branch' => $request->branch,
            'current_year' => $request->current_year,
            'internship_type' => $request->internship_type,
            'stipend_amount' => $request->stipend_amount,
            'document_file' => $document,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data updated successfully',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {

        $data = EmployeeTrainingInternship::find($id);

        if (!$data) {

            return response()->json([
                'status' => false,
                'message' => 'Record not found'
            ]);
        }

        $data->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully'
        ]);
    }
}