<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'leave_data', 'emp_name', 'overall_total_leaves', 'taken', 'pending'
    ];

    // public function employee(){
    //     return $this->belongsTo(Employee::class);
    // }
    public function employee(){
        return $this->belongsTo(Employee::class, 'user_id'); // Assuming user_id is the foreign key
    }
    
}
