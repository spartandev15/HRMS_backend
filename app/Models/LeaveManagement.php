<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveManagement extends Model
{
    use HasFactory;

    protected $table = 'leave_managements';
    protected $fillable = [
        'user_id',  
        'start_date',
        'end_date',
        'leave_type',
        'reason',
        'status'
    ];

    // One-to-One relationship with the User (assumed as a user-related table)
    public function employee_detail()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id'); // Assuming user_id in LeaveManagement refers to user_id in Employee
    }
}
