<?php

namespace Tests\Unit\Wallet;

use App\Modules\Wallet\Services\BankCatalogService;
use Tests\TestCase;

class BankCatalogServiceTest extends TestCase
{
    public function test_filters_allowed_banks_and_excludes_heritage(): void
    {
        $service = app(BankCatalogService::class);

        $filtered = $service->filterAllowed([
            ['name' => 'Access Bank', 'code' => '044'],
            ['name' => 'Heritage Bank', 'code' => '030'],
            ['name' => 'Random Microfinance Bank', 'code' => '999'],
            ['name' => 'Guaranty Trust Bank', 'code' => '058'],
            ['name' => 'OPay Digital Services Limited (OPay)', 'code' => '100004'],
        ]);

        $names = collect($filtered)->pluck('name')->all();

        $this->assertContains('Access Bank', $names);
        $this->assertContains('Guaranty Trust Bank', $names);
        $this->assertContains('OPay Digital Services Limited (OPay)', $names);
        $this->assertNotContains('Heritage Bank', $names);
        $this->assertNotContains('Random Microfinance Bank', $names);
    }
}
