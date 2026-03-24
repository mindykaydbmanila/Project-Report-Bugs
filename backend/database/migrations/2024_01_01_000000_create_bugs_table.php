<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['Critical', 'High', 'Medium', 'Low'])->default('Medium');
            $table->enum('scenario_type', ['UI', 'Functionality', 'UI & Functionality'])->default('UI');
            $table->enum('status', ['Pending', 'Out of Scope', 'Ongoing', 'Completed'])->default('Pending');
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bugs');
    }
};
