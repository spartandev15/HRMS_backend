<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees';
    protected $fillable = [
       
        'user_id' ,
        'first_name',
        'last_name' ,
        'line_manager' ,
        'email' ,
        'designation',
        'employee_id',
        'email' ,
        'joining_date' ,
        'phone' ,
        'password',
    ];

}
