<?php

namespace Tests\Unit;

use App\Modules\Wallet\Services\Blockchain\MonitoredNetworkCatalog;
use Tests\TestCase;

class MonitoredNetworkCatalogTest extends TestCase
{
    public function test_resolves_aliases_to_network_ids(): void
    {
        $catalog = app(MonitoredNetworkCatalog::class);

        $this->assertSame('bitcoin', $catalog->resolveId('Bitcoin'));
        $this->assertSame('ethereum', $catalog->resolveId('ERC20'));
        $this->assertSame('bsc', $catalog->resolveId('BEP20'));
        $this->assertSame('polygon', $catalog->resolveId('Polygon'));
        $this->assertSame('tron', $catalog->resolveId('TRC20'));
        $this->assertSame('solana', $catalog->resolveId('Solana'));
        $this->assertSame('Ethereum (ERC20)', $catalog->label('ethereum'));
        $this->assertSame('TRON (TRC20)', $catalog->label('tron'));
    }

    public function test_display_provider_hides_mempool_brand(): void
    {
        $catalog = app(MonitoredNetworkCatalog::class);

        $this->assertSame('Public explorer', $catalog->displayProvider('bitcoin', 'native', 'mempool'));
        $this->assertSame('Blockchain.com', $catalog->displayProvider('bitcoin', 'blockchain_com', 'blockchain_com'));
        $this->assertSame('Native (TRON)', $catalog->displayProvider('tron', 'blockchain_com', 'trongrid'));
        $this->assertSame('Native (fallback)', $catalog->displayProvider('polygon', 'blockchain_com', 'etherscan'));
    }
}
