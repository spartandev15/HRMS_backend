<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
      */
    public function up(): void
    {
        Schema::create('job_details', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('job_title');
            $table->string('job_category');
            $table->string('line_member');
            $table->string('join_date');
            $table->string('employement_status');
            $table->string('education_level');
            $table->string('education_institude');
            $table->string('education_year');
            $table->string('education_score');
            $table->string('work_experience_company');
            $table->string('work_experience_job_title');
            $table->string('work_experience_from');
            $table->string('work_experience_to');
            $table->string('salary_component');
            $table->string('salary_pay_frequency');
            $table->string('salary_currency');
            $table->string('salary_amount');
            $table->string('salary_account_number');
            $table->string('salary_account_type');
            $table->string('salary_bank_name');
            $table->string('salary_ifsc_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_details');
    }
};
