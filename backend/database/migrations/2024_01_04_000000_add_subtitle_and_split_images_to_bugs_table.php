<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->json('subtitles')->nullable()->after('title');
            $table->json('frontend_images')->nullable()->after('images');
            $table->json('cms_images')->nullable()->after('frontend_images');
        });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn(['subtitles', 'frontend_images', 'cms_images']);
        });
    }
};
