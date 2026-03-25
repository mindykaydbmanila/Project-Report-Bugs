<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->json('assigned_developers')->nullable()->after('assigned_developer_id');
        });

        // Migrate existing single-developer data into the new JSON array
        DB::table('bugs')->whereNotNull('assigned_developer_id')->chunkById(100, function ($bugs) {
            foreach ($bugs as $bug) {
                $user = DB::table('users')->where('id', $bug->assigned_developer_id)->first();
                if ($user) {
                    DB::table('bugs')->where('id', $bug->id)->update([
                        'assigned_developers' => json_encode([[
                            'id'     => $user->id,
                            'name'   => $user->name,
                            'email'  => $user->email,
                            'avatar' => $user->avatar,
                        ]]),
                    ]);
                }
            }
        });

        // Migrate guest email assignments
        DB::table('bugs')
            ->whereNull('assigned_developer_id')
            ->whereNotNull('guest_developer_email')
            ->chunkById(100, function ($bugs) {
                foreach ($bugs as $bug) {
                    $name = $bug->guest_developer_name ?? explode('@', $bug->guest_developer_email)[0];
                    DB::table('bugs')->where('id', $bug->id)->update([
                        'assigned_developers' => json_encode([[
                            'id'     => null,
                            'name'   => $name,
                            'email'  => $bug->guest_developer_email,
                            'avatar' => null,
                        ]]),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn('assigned_developers');
        });
    }
};
