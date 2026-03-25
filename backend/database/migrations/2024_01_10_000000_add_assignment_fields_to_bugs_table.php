<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_developer_id')->nullable()->after('project_id');
            $table->foreign('assigned_developer_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('ticket_sent_at')->nullable()->after('assigned_developer_id');
            $table->json('activity_log')->nullable()->after('dev_comments');
        });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropForeign(['assigned_developer_id']);
            $table->dropColumn(['assigned_developer_id', 'ticket_sent_at', 'activity_log']);
        });
    }
};
