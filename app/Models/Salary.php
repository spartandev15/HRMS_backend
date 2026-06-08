<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $table = 'salaries';

    protected $fillable = [
        'employee_name', 'user_id', 'basic_salary', 'house_rent', 'medical_allowance',
        'tax', 'leave_deduction', 'pf', 'employee_state', 'insurance', 'extra_working',
        'gross_total', 'final_total', 'gross_salary', 'bank_name', 'bank_ifsc', 'account_number',
        'account_holder_name',
    ];

    public function employee() {
        return $this->belongsTo(Employee::class, 'user_id', 'id');
    }
}

