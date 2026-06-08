<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'first_name','last_name', 'email', 'password', 'last_name', 'organisation', 
        'organisation_id', 'address', 'payment', 'employee_id', 'status',
        'line_manager', 'designation', 'joining_date','orpect_user_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function userDetail(){
        return $this->hasOne(User_Detail::class, 'user_id', 'id');
    }

    public function jobDetail(){
        return $this->hasOne(JobDetail::class, 'user_id', 'id');
    }

    public function leave(){
        return $this->hasOne(Leave::class);  // One-to-One relationship with Leave model
    }

    public function employee(){
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    public function salary(){
        return $this->hasOne(Salary::class, 'user_id', 'id');
    }
}

