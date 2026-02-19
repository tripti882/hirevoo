<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('user_id');
            $table->string('job_type')->nullable()->after('title'); // full_time, part_time, contract, internship, temporary, volunteer, other
            $table->boolean('is_night_shift')->default(false)->after('job_type');
            $table->string('work_location_type')->nullable()->after('location'); // office, remote, hybrid
            $table->string('pay_type')->nullable()->after('work_location_type'); // fixed, hourly, negotiable, not_disclosed, other
            $table->text('perks')->nullable()->after('pay_type');
            $table->boolean('joining_fee_required')->default(false)->after('perks');
        });
    }

    public function down(): void
    {
        Schema::table('employer_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'job_type', 'is_night_shift', 'work_location_type',
                'pay_type', 'perks', 'joining_fee_required',
            ]);
        });
    }
};
