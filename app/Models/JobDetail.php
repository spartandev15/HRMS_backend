<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobDetail extends Model
{
    use HasFactory;
    protected $table = 'job_details';
    protected $fillable = [
        'job_title',
        'job_category',
        'line_member',
        'join_date',
        'employement_status',
        'user_id',
        'education_level',
        'education_institude',
        'education_year',
        'education_score',
        'work_experience_company',
        'work_experience_job_title',
        'work_experience_from',
        'work_experience_to',
        'salary_component',
        'salary_pay_frequency',
        'salary_currency',
        'employment_status',
        'salary_amount',
        'salary_account_number',
        'salary_account_type',
        'salary_bank_name',
        'salary_ifsc_code',
    ];
}
     