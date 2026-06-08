<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRecord extends Model
{
    use HasFactory;

    // Specify the table name (optional, if the table name is different from plural form of model name)
    protected $table = 'overtime_records';

    // Define the fields that are mass-assignable
    protected $fillable = [
        'user_id',         // The user that the overtime record belongs to
        'overtime_date',   // Date of overtime
        'working_hours',   // Hours worked
        'salary_per_hour', // Salary per hour
        'final_balance',   // Final balance for the month
        'project_name',    // Name of the project
        'project_url',     // URL for the project
        'screenshot',      // Path to the uploaded screenshot
        'status',
    ];

    // Optional: You can define a relationship to the User model (if needed)
    // In case you want to link the overtime records to a specific user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
