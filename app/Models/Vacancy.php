<?php
// App/Models/Vacancy.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use HasFactory;
protected $table = "vacancy_details";
    protected $fillable = [
        'job_title',
        'location',
        'skills_required',
        'salary_range',
        'job_type',
        'company_information',
        'job_responsibilities',
        'contact_email',
        'experience',
        'joining_time',
        'status'
    ];
}

