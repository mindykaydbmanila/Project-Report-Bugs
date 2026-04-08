<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_projects', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('color');
            $table->date('contract_start')->nullable()->after('is_active');
            $table->date('contract_end')->nullable()->after('contract_start');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_projects', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'contract_start', 'contract_end']);
        });
    }
};
