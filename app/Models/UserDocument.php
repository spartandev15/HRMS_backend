<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'aadhaar_card',
        'pan_card',
        'tenth_dmc',
        'twelfth_dmc',
        'college_degree',
        'user_photo',
        'bank_details',
        'previous_experience',
        'previous_salary_slip',
        'status',
    ];

    // Cast the fields to array when retrieved from the database
    protected $casts = [
        'aadhaar_card' => 'array',  // Documents will be cast to array
        'pan_card' => 'array',
        'tenth_dmc' => 'array',
        'twelfth_dmc' => 'array',
        'college_degree' => 'array',
        'user_photo' => 'array',
        'bank_details' => 'array',
        'previous_experience' => 'array',
        'previous_salary_slip' => 'array',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
