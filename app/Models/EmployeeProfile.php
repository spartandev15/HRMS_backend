<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;
    protected $table = 'employeeprofile';
    protected $fillable = ['name', 'user_id'];
   
}
