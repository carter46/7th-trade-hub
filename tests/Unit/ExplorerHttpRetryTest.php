<?php

namespace Tests\Unit;

use App\Modules\Wallet\Services\Blockchain\ExplorerHttp;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExplorerHttpRetryTest extends TestCase
{
    public function test_retries_on_server_error_then_succeeds(): void
    {
        Http::fake([
            'https://example.test/*' => Http::sequence()
                ->push('fail', 500)
                ->push('fail', 500)
                ->push(['ok' => true], 200),
        ]);

        $result = app(ExplorerHttp::class)->get('https://example.test/api', [], [], 3);

        $this->assertTrue($result['ok']);
        $this->assertSame(3, $result['attempts']);
        Http::assertSentCount(3);
    }

    public function test_fails_after_max_retries(): void
    {
        Http::fake([
            'https://example.test/*' => Http::response('down', 503),
        ]);

        $result = app(ExplorerHttp::class)->get('https://example.test/api', [], [], 3);

        $this->assertFalse($result['ok']);
        $this->assertSame(3, $result['attempts']);
    }
}
