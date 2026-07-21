<?php

namespace Tests\Unit;

use App\Support\DashboardNavigation;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    public function test_root_routes_match_exactly_and_not_every_dashboard_page(): void
    {
        $adminOverview = [
            'route' => 'admin',
            'match' => ['admin'],
        ];
        $userDashboard = [
            'route' => 'dashboard',
            'match' => ['dashboard'],
        ];

        $this->assertTrue(DashboardNavigation::isActive($adminOverview, 'admin'));
        $this->assertFalse(DashboardNavigation::isActive($adminOverview, 'admin.users'));
        $this->assertTrue(DashboardNavigation::isActive($userDashboard, 'dashboard'));
        $this->assertFalse(DashboardNavigation::isActive($userDashboard, 'dashboard.wallet'));
    }

    public function test_explicit_child_patterns_match_nested_pages(): void
    {
        $support = [
            'route' => 'dashboard.support.index',
            'match' => ['dashboard.support.*'],
        ];
        $products = [
            'route' => 'admin.platform-products',
            'match' => ['admin.platform-products', 'admin.platform-products.*'],
        ];

        $this->assertTrue(DashboardNavigation::isActive($support, 'dashboard.support.show'));
        $this->assertTrue(DashboardNavigation::isActive($products, 'admin.platform-products.edit'));
        $this->assertFalse(DashboardNavigation::isActive($products, 'admin.platform-categories'));
    }

    public function test_active_child_opens_its_parent_group(): void
    {
        $entries = DashboardNavigation::for('admin');
        $open = DashboardNavigation::initiallyOpenGroups($entries, 'admin.users');

        $this->assertContains('users', $open);
        $this->assertNotContains('dashboard', $open);
    }

    public function test_all_configured_menu_routes_and_icons_exist(): void
    {
        foreach (['admin', 'user'] as $role) {
            foreach (DashboardNavigation::for($role) as $entry) {
                $items = ($entry['type'] ?? 'link') === 'group'
                    ? ($entry['children'] ?? [])
                    : [$entry];

                if (isset($entry['icon'])) {
                    $this->assertFileExists(resource_path('icons/'.$entry['icon'].'.svg'));
                }

                foreach ($items as $item) {
                    $this->assertTrue(Route::has($item['route']), "Missing route [{$item['route']}]");
                    $this->assertFileExists(resource_path('icons/'.$item['icon'].'.svg'));
                }
            }
        }
    }
}
