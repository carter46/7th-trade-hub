<?php

namespace Database\Seeders\Demo\Support;

class DemoAdminCatalog
{
    /**
     * @return list<array{key: string, name: string, email: string, username: string, permissions: list<string>}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'super',
                'name' => 'Super Admin',
                'email' => 'super.admin@example.com',
                'username' => 'superadmin',
                'permissions' => [
                    'admins.manage', 'users.manage', 'finance.manage', 'support.manage',
                    'catalog.manage', 'compliance.manage', 'system.manage', 'analytics.view',
                ],
            ],
            [
                'key' => 'finance',
                'name' => 'Finance Admin',
                'email' => 'finance.admin@example.com',
                'username' => 'financeadmin',
                'permissions' => ['finance.manage', 'analytics.view'],
            ],
            [
                'key' => 'compliance',
                'name' => 'Compliance Admin',
                'email' => 'compliance.admin@example.com',
                'username' => 'complianceadmin',
                'permissions' => ['compliance.manage', 'users.manage'],
            ],
            [
                'key' => 'support',
                'name' => 'Support Admin',
                'email' => 'support.admin@example.com',
                'username' => 'supportadmin',
                'permissions' => ['support.manage'],
            ],
            [
                'key' => 'moderator',
                'name' => 'Marketplace Moderator',
                'email' => 'moderator@example.com',
                'username' => 'moderator',
                'permissions' => ['catalog.manage'],
            ],
        ];
    }
}
