<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('user_world_progress')->get();

        foreach ($rows as $row) {
            DB::table('user_world_level_progress')->updateOrInsert(
                [
                    'user_id' => $row->user_id,
                    'level_id' => $row->level_id,
                ],
                [
                    'completed_at' => $row->completed_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
            );
        }
    }

    public function down(): void
    {
        // No revert — datos de progreso preservados.
    }
};
