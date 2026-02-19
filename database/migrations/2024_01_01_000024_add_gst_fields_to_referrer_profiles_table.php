<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrer_profiles', function (Blueprint $table) {
            $table->string('gstin', 20)->nullable()->after('department');
            $table->boolean('gst_verified')->default(false)->after('gstin');
            $table->string('company_legal_name')->nullable()->after('gst_verified');
            $table->text('company_address')->nullable()->after('company_legal_name');
            $table->boolean('invoice_consent')->default(false)->after('company_address');
        });
    }

    public function down(): void
    {
        Schema::table('referrer_profiles', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'gst_verified', 'company_legal_name', 'company_address', 'invoice_consent']);
        });
    }
};
