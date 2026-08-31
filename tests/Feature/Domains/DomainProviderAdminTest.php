<?php

namespace Tests\Feature\Domains;

use App\Models\DomainProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DomainProviderAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_provider_credentials(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $provider = DomainProvider::query()->where('key', 'namecom')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.domain-providers.update', $provider), [
                'enabled' => '1',
                'is_default' => '1',
                'sandbox' => '1',
                'credentials' => [
                    'username' => 'admin-user',
                    'api_token' => 'admin-token',
                ],
            ])
            ->assertRedirect(route('admin.domain-providers'));

        $provider->refresh();
        $this->assertTrue($provider->enabled);
        $this->assertSame('admin-user', $provider->credentials['username'] ?? null);
    }

    public function test_admin_test_connection_updates_health(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $provider = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $provider->update([
            'enabled' => true,
            'credentials' => ['username' => 'test', 'api_token' => 'secret'],
            'sandbox' => true,
        ]);

        Http::fake([
            'https://api.dev.name.com/core/v1/hello' => Http::response(['message' => 'ok']),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.domain-providers.test', $provider))
            ->assertRedirect();

        $this->assertSame(DomainProvider::HEALTH_HEALTHY, $provider->fresh()->health_status);
    }

    public function test_fallback_priority_must_be_unique(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $primary = DomainProvider::query()->where('key', 'namecom')->firstOrFail();
        $backup = DomainProvider::query()->create([
            'key' => 'namecom-backup-test',
            'display_name' => 'Backup',
            'adapter_class' => \App\Services\Domains\Providers\NameCom\NameComProvider::class,
            'enabled' => true,
            'is_default' => false,
            'fallback_priority' => 5,
            'sandbox' => true,
            'capabilities' => [],
            'credentials' => ['username' => 'a', 'api_token' => 'b'],
            'health_status' => 'unknown',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.domain-providers.update', $primary), [
                'enabled' => '1',
                'is_default' => '0',
                'fallback_priority' => '5',
                'sandbox' => '1',
                'credentials' => [
                    'username' => 'a',
                    'api_token' => 'b',
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
