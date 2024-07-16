<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveManagement extends Model
{
    use HasFactory;
    protected $table = 'leave_management';
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'leave_type',
        'reason'
    ];
}
