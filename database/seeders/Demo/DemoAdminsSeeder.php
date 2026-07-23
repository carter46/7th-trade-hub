<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use Database\Seeders\Demo\Support\DemoAdminCatalog;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAdminsSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        foreach (DemoAdminCatalog::all() as $row) {
            $createdAt = $timeline->monthsAgo(10);

            $roleName = $row['key'] === 'super' ? 'admin' : 'demo_'.$row['key'];
            $role = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
            // Never syncPermissions on the shared `admin` role — PermissionSeeder owns that.
            if ($roleName !== 'admin') {
                $role->syncPermissions($row['permissions']);
            }

            $user = User::query()->firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => $createdAt,
                    'profile_completed_at' => $createdAt,
                    'terms_accepted_at' => $createdAt,
                ]
            );

            $user->forceFill([
                'name' => $row['name'],
                'username' => $row['username'],
                'kyc_level' => 2,
                'is_suspended' => false,
            ])->save();

            $user->syncRoles([$roleName]);
            if ($row['key'] === 'super') {
                $user->givePermissionTo('admins.manage');
            }

            $timeline->stamp($user, $createdAt, [
                'email_verified_at' => $createdAt,
                'profile_completed_at' => $createdAt,
                'terms_accepted_at' => $createdAt,
            ]);

            $ctx->registerAdmin($row['key'], $user->fresh());
        }

        $ctx->note('✓ Admin personas created (Super / Finance / Compliance / Support / Moderator)');
    }
}
