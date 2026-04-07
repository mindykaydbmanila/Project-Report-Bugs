<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');          // ready_for_qa | ticket_created | ticket_completed | developer_assigned | blocked
            $table->string('title');
            $table->string('message');
            $table->json('data')->nullable(); // { bug_id, bug_sequence, bug_title, project_name }
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
