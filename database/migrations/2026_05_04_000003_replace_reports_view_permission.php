<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyPermissionId = DB::table('permissions')
            ->where('name', 'reports.view')
            ->value('id');

        if (! $legacyPermissionId) {
            return;
        }

        DB::table('permission_role')
            ->where('permission_id', $legacyPermissionId)
            ->delete();

        DB::table('permissions')
            ->where('id', $legacyPermissionId)
            ->delete();
    }

    public function down(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'reports.view'],
            [
                'label' => 'Ver reportes',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
};

