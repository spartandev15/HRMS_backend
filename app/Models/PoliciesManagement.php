<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliciesManagement extends Model
{
    use HasFactory;
    protected $table = 'policies_management';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'exception'
    ];
}  
