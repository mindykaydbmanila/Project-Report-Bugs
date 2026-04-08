<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep only the earliest folder per email, delete the rest
        $emails = DB::table('dev_folders')->select('developer_email')->distinct()->pluck('developer_email');
        foreach ($emails as $email) {
            $ids = DB::table('dev_folders')
                ->where('developer_email', $email)
                ->orderBy('id')
                ->pluck('id');
            if ($ids->count() > 1) {
                DB::table('dev_folders')
                    ->whereIn('id', $ids->slice(1)->values())
                    ->delete();
            }
        }

        // Drop project_id column and add unique constraint on developer_email
        Schema::table('dev_folders', function (Blueprint $table) {
            $table->dropColumn('project_id');
            $table->unique('developer_email');
        });
    }

    public function down(): void
    {
        Schema::table('dev_folders', function (Blueprint $table) {
            $table->dropUnique(['developer_email']);
            $table->unsignedBigInteger('project_id')->nullable();
        });
    }
};
