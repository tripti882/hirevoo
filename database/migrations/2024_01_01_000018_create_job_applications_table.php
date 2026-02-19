<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->text('cover_message')->nullable();
            $table->string('status')->default('applied'); // applied, viewed, shortlisted, rejected
            $table->timestamps();
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['user_id', 'job_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
