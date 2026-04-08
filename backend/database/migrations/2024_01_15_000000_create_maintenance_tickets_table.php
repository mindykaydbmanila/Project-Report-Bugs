<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_project_id');
            $table->integer('sequence')->default(1);
            $table->string('client');
            $table->text('request');
            $table->string('sent_thru')->default('Email');
            $table->date('date_received')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->json('assigned_devs')->nullable();
            $table->json('assigned_qa')->nullable();
            $table->string('status')->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->foreign('maintenance_project_id')
                  ->references('id')
                  ->on('maintenance_projects')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tickets');
    }
};
