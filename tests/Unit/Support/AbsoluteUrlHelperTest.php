<?php

namespace Tests\Unit\Support;

use Tests\TestCase;

class AbsoluteUrlHelperTest extends TestCase
{
    public function test_absolute_url_prefixes_root_relative_media_paths(): void
    {
        config(['app.url' => 'https://7th-tradehub.online']);

        $this->assertSame(
            'https://7th-tradehub.online/storage/media/logo.png',
            absolute_url('/storage/media/logo.png'),
        );
    }

    public function test_absolute_url_leaves_fully_qualified_urls_unchanged(): void
    {
        $url = 'https://cdn.example.com/logo.png';

        $this->assertSame($url, absolute_url($url));
    }

    public function test_absolute_media_url_from_id_returns_null_without_media(): void
    {
        $this->assertNull(absolute_media_url_from_id(null));
    }
}
