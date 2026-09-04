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
        Schema::table('deletion_records', function (Blueprint $table): void {
            $table->string('ip_address', 60)->nullable()->after('deleted_by_role');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('channel', 30)->default('Web')->after('user_agent');
            $table->string('action_type', 40)->default('SOFT_DELETE')->after('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deletion_records', function (Blueprint $table): void {
            $table->dropColumn(['ip_address', 'user_agent', 'channel', 'action_type']);
        });
    }
};
