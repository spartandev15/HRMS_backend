<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',         // The title of the notice
        'description',   // The description of the notice
        'attachment',    // The attachment path (photo, video, document)
        'email',         // A list of employee emails
    ];
    
}
