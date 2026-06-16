<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_name',
        'email',
        'phone_number',
        'resume_file',
        'position',
        'interview_type',
        'interview_date',
        'interview_time',
        'interviewer_name',
        'interviewer_email',
        'orpect_user_id',
    ];

}
