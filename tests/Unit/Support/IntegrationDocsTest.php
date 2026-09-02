<?php

namespace Tests\Unit\Support;

use App\Support\IntegrationDocs;
use Tests\TestCase;

class IntegrationDocsTest extends TestCase
{
    public function test_resolve_readme_markdown(): void
    {
        $document = app(IntegrationDocs::class)->resolve('README');

        $this->assertNotNull($document);
        $this->assertSame('md', $document['extension']);
        $this->assertStringContainsString('Site Integration Platform', $document['html'] ?? '');
    }

    public function test_resolve_rejects_unlisted_extension(): void
    {
        $this->assertNull(app(IntegrationDocs::class)->resolve('.env'));
    }

    public function test_public_path_strips_md_extension(): void
    {
        $document = app(IntegrationDocs::class)->resolve('MERCHANT-GUIDE');

        $this->assertNotNull($document);
        $this->assertSame('MERCHANT-GUIDE', $document['path']);
    }
}
