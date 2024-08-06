<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class TimerImage extends Model
{
    use HasFactory;
    protected $table = 'timer_image';
    protected $fillable = [
        'user_id',
        'project_id',
        'timer_id',
        'image'
    ];
}
     