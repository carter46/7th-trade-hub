<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceCategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_catalog_manage(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $admin->roles->each->revokePermissionTo('catalog.manage');
        $admin->unsetRelation('roles');
        $admin->unsetRelation('permissions');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->assertFalse($admin->fresh()->can('catalog.manage'));

        $this->actingAs($admin->fresh())
            ->get(route('admin.marketplace-categories'))
            ->assertForbidden();
    }

    public function test_can_create_and_update_category(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->post(route('admin.marketplace-categories.store'), [
                'name' => 'Electronics',
                'sort_order' => 5,
            ])
            ->assertRedirect(route('admin.marketplace-categories'));

        $category = Category::where('name', 'Electronics')->first();
        $this->assertNotNull($category);
        $this->assertSame(5, $category->sort_order);
        $this->assertTrue($category->is_active);

        $this->actingAs($admin)
            ->put(route('admin.marketplace-categories.update', $category), [
                'name' => 'Gadgets',
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.marketplace-categories'));

        $this->assertSame('Gadgets', $category->fresh()->name);
        $this->assertSame(2, $category->fresh()->sort_order);
    }

    public function test_can_toggle_category(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $category = Category::create([
            'name' => 'Books',
            'slug' => 'books-test',
            'type' => 'marketplace',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.marketplace-categories.toggle', $category))
            ->assertRedirect();

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_domain_nav_groups_render(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertOk()
            ->assertSee('Platform Services')
            ->assertSee('Crypto Exchange')
            ->assertSee('Identity & Users');
    }
}
