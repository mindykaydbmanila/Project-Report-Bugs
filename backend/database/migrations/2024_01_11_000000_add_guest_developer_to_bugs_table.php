<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->string('guest_developer_email')->nullable()->after('assigned_developer_id');
            $table->string('guest_developer_name')->nullable()->after('guest_developer_email');
        });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn(['guest_developer_email', 'guest_developer_name']);
        });
    }
};
