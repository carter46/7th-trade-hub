<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Capability permissions for future role bundles.
     * Super Admin / Finance Admin / etc. are not UI roles yet — they will map to these later.
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
        'admins.manage',
        'users.manage',
        'finance.manage',
        'support.manage',
        'catalog.manage',
        'compliance.manage',
        'system.manage',
        'analytics.view',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Keep admins.manage as a direct permission so it can be granted optionally
        // to individual administrators without every admin inheriting it via role.
        $rolePermissions = array_values(array_filter(
            self::PERMISSIONS,
            fn (string $name): bool => $name !== 'admins.manage',
        ));
        $adminRole->syncPermissions($rolePermissions);

        // Bootstrap: ensure at least one non-suspended admin can manage administrators.
        // Do not copy role permissions onto users as direct grants — that makes role
        // revocation ineffective and fights the direct-only admins.manage model.
        if (User::permission('admins.manage')->role('admin')->where('is_suspended', false)->count() === 0) {
            User::role('admin')->orderBy('id')->first()?->givePermissionTo('admins.manage');
        }
    }
}
