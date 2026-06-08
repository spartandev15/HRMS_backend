<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User_Detail;
use App\Models\Leave;
use App\Models\LeaveManagement;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'line_manager', 'email', 'designation',
        'employee_id', 'email', 'joining_date', 'phone', 'password', 'date_of_birth','profile_photo','orpect_employee_id','orpect_user_id'
    ];

    public function userDetail(){
        return $this->hasOne(User_Detail::class, 'user_id', 'user_id');
    }

    public function leaves(){
        return $this->hasOne(Leave::class, 'user_id', 'user_id');
    }

    public function leaveManagement(){
        return $this->hasMany(LeaveManagement::class, 'user_id', 'user_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function salary(){
        return $this->hasOne(Salary::class, 'user_id', 'user_id');
    }
}
