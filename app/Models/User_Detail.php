<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_Detail extends Model
{
    use HasFactory; 
    protected $table = 'user_details';
    protected $fillable = [
        'user_id',
        'gender',
        'dob',
        'job_title',
        'department',
        'joining_date',
        'emp_id',
        'profile_photo',
    ];

}
