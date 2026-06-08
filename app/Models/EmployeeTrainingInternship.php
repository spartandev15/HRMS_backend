<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTrainingInternship extends Model
{
    use HasFactory;

    protected $table = 'employee_training_internships';

    protected $fillable = [

        'first_name',
        'last_name',
        'email',
        'address',
        'designation',
        'date_of_birth',
        'user_id',
       
        'joining_date',
        'expected_end_date',
        'department',
        'mentor',

        'work_location',
        'training_program_name',
        'training_type',
        'duration_in_months',
        'skills_to_learn',
        'has_prior_experience',

        'work_mode',
        'university_name',
        'college_name',
        'course_name',
        'branch',
        'current_year',
        'internship_type',
        'stipend_amount',

        'document_file',
        'status',
    ];
}
