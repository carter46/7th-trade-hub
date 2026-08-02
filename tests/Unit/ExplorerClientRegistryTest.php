<?php

namespace Tests\Unit;

use App\Models\IntegrationProvider;
use App\Modules\Wallet\Services\Blockchain\BlockchainComClient;
use App\Modules\Wallet\Services\Blockchain\EtherscanClient;
use App\Modules\Wallet\Services\Blockchain\ExplorerClientRegistry;
use App\Modules\Wallet\Services\Blockchain\MempoolBitcoinClient;
use App\Modules\Wallet\Services\Blockchain\SolanaRpcClient;
use App\Modules\Wallet\Services\Blockchain\TronGridClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExplorerClientRegistryTest extends TestCase
{
    use RefreshDatabase;

    private function setProvider(string $provider): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $row->meta = array_merge($row->meta ?? [], ['monitor_provider' => $provider]);
        $row->mergeCredentials([
            'etherscan_api_key' => 'eth-key',
            'trongrid_api_key' => 'tron-key',
            'blockchain_com_api_key' => 'expl_test',
        ]);
        $row->save();
    }

    public function test_native_resolves_expected_clients(): void
    {
        $this->setProvider('native');
        $registry = app(ExplorerClientRegistry::class);

        $this->assertInstanceOf(MempoolBitcoinClient::class, $registry->resolve('Bitcoin')['client']);
        $this->assertInstanceOf(EtherscanClient::class, $registry->resolve('ERC20')['client']);
        $this->assertInstanceOf(EtherscanClient::class, $registry->resolve('BEP20')['client']);
        $this->assertInstanceOf(TronGridClient::class, $registry->resolve('TRC20')['client']);
        $this->assertInstanceOf(SolanaRpcClient::class, $registry->resolve('Solana')['client']);
        $this->assertSame('bitcoin', $registry->resolve('Bitcoin')['network_id']);
        $this->assertSame('bep20', $registry->resolve('BEP20')['network_id']);
    }

    public function test_blockchain_com_resolves_gateway_for_btc_eth_sol_and_tron_always_trongrid(): void
    {
        $this->setProvider('blockchain_com');
        $registry = app(ExplorerClientRegistry::class);

        $this->assertInstanceOf(BlockchainComClient::class, $registry->resolve('Bitcoin')['client']);
        $this->assertInstanceOf(BlockchainComClient::class, $registry->resolve('Ethereum')['client']);
        $this->assertInstanceOf(BlockchainComClient::class, $registry->resolve('Solana')['client']);
        $this->assertInstanceOf(TronGridClient::class, $registry->resolve('TRC20')['client']);
        $this->assertSame('trongrid', $registry->resolve('TRC20')['client_key']);
    }

    public function test_blockchain_com_falls_back_to_etherscan_for_polygon(): void
    {
        $this->setProvider('blockchain_com');
        $resolved = app(ExplorerClientRegistry::class)->resolve('Polygon');

        $this->assertInstanceOf(EtherscanClient::class, $resolved['client']);
        $this->assertSame('etherscan', $resolved['client_key']);
        $this->assertSame('native', $resolved['provider']);
        $this->assertSame('polygon', $resolved['network_id']);
    }
}

class BlockchainComClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetches_bitcoin_incoming_via_gateway(): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $row->mergeCredentials(['blockchain_com_api_key' => 'expl_test_key']);
        $row->meta = ['monitor_provider' => 'blockchain_com'];
        $row->save();

        Http::fake([
            'api.blockchain.info/explorer-gateway-kt/btc/address/transactions' => Http::response([
                'transactions' => [
                    [
                        'txId' => 'abc123',
                        'blockHeight' => 100,
                        'confirmations' => 3,
                        'outputs' => [
                            ['address' => 'bc1qtest', 'value' => 150000],
                        ],
                        'inputs' => [
                            ['address' => 'bc1qfrom'],
                        ],
                    ],
                ],
            ], 200),
            'api.blockchain.info/explorer-gateway-kt/btc/blocks' => Http::response([
                'currentHeight' => 102,
            ], 200),
            'api.blockchain.info/*' => Http::response(['currentHeight' => 102], 200),
        ]);

        $client = app(BlockchainComClient::class);
        $transfers = $client->fetchIncoming('bc1qtest', 'BTC', 'Bitcoin');

        $this->assertCount(1, $transfers);
        $this->assertSame('abc123', $transfers[0]['tx_hash']);
        $this->assertEqualsWithDelta(0.0015, $transfers[0]['amount'], 0.0000001);
        $this->assertSame(100, $transfers[0]['block_height']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Explorer-Auth-Key', 'expl_test_key')
                && str_contains($request->url(), '/btc/address/transactions');
        });
    }

    public function test_health_check_uses_tip_endpoint(): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::BLOCKCHAIN_MONITORING);
        $row->mergeCredentials(['blockchain_com_api_key' => 'expl_test_key']);
        $row->save();

        Http::fake([
            'api.blockchain.info/explorer-gateway-kt/btc/blocks' => Http::response([
                'currentHeight' => 900000,
            ], 200),
        ]);

        $this->assertTrue(app(BlockchainComClient::class)->healthCheck());
        $this->assertSame(900000, app(BlockchainComClient::class)->tipHeight('Bitcoin'));
    }
}
