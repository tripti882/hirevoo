<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_job_id')->constrained('employer_jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->text('cover_message')->nullable();
            $table->string('status')->default('applied');
            $table->timestamps();
        });

        Schema::table('employer_job_applications', function (Blueprint $table) {
            $table->unique(['employer_job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_job_applications');
    }
};
