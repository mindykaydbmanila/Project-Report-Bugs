<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_project_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('invited_email');
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
            $table->enum('permission', ['viewer', 'commenter', 'editor'])->default('viewer');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['maintenance_project_id', 'invited_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_project_shares');
    }
};
