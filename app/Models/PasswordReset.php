<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // Specify the table name (optional, Laravel assumes 'password_resets')
    protected $table = 'password_resets';

    // Disable automatic primary key handling because the table doesn't have an 'id' column
    public $incrementing = false; // Disable auto-incrementing
    protected $primaryKey = 'email'; // Replace 'your_custom_column' with the name of your actual primary key


    // Define the fillable fields
    protected $fillable = ['email', 'token', 'created_at'];

    // Disable timestamps if you're not using the updated_at field
    public $timestamps = false;
}