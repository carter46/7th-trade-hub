<?php

namespace Tests\Unit;

use App\Modules\Wallet\Services\NetworkRegistry;
use Tests\TestCase;

class NetworkRegistryTest extends TestCase
{
    public function test_same_network_aliases(): void
    {
        $registry = app(NetworkRegistry::class);

        $this->assertTrue($registry->sameNetwork('ERC20', 'ethereum'));
        $this->assertTrue($registry->sameNetwork('TRC20', 'tron'));
        $this->assertTrue($registry->sameNetwork('BEP20', 'bsc'));
        $this->assertFalse($registry->sameNetwork('bitcoin', 'ethereum'));
    }

    public function test_checkbox_options_expose_labels_not_as_primary(): void
    {
        $options = app(NetworkRegistry::class)->checkboxOptions();
        $labels = collect($options)->pluck('label')->all();

        $this->assertContains('Ethereum (ERC20)', $labels);
        $this->assertContains('Arbitrum One', $labels);
        $this->assertTrue(collect($options)->every(fn ($o) => isset($o['explorer'], $o['monitorable'])));
    }
}
