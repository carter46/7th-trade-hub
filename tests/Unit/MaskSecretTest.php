<?php

namespace Tests\Unit;

use Tests\TestCase;

class MaskSecretTest extends TestCase
{
    public function test_masks_long_secrets_with_prefix_and_suffix(): void
    {
        $this->assertSame('expl*********xyz9', mask_secret('expl_live_abcxyz9'));
    }

    public function test_masks_short_secrets(): void
    {
        $this->assertSame('a****z', mask_secret('abcz'));
    }

    public function test_empty_returns_null(): void
    {
        $this->assertNull(mask_secret(null));
        $this->assertNull(mask_secret(''));
        $this->assertNull(mask_secret('   '));
    }
}
