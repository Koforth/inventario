<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LEGACY_PERMISSIONS = [
        'view-catalog',
        'create-products',
        'manage-products',
        'manage-inventory',
        'manage-quotes',
        'manage-purchases',
        'manage-cash',
        'view-reports',
        'manage-users',
    ];

    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', self::LEGACY_PERMISSIONS)
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        DB::table('permission_role')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
