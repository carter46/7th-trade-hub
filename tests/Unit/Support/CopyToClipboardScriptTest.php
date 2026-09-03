<?php

namespace Tests\Unit\Support;

use Tests\TestCase;

class CopyToClipboardScriptTest extends TestCase
{
    public function test_helper_uses_clipboard_api_then_temporary_textarea_fallback(): void
    {
        $path = resource_path('js/copy-to-clipboard.js');
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);

        $this->assertStringContainsString('navigator.clipboard.writeText', $source);
        $this->assertStringContainsString('ClipboardItem', $source);
        $this->assertStringContainsString('copyFromAsync', $source);
        $this->assertStringContainsString("document.execCommand('copy')", $source);
        $this->assertStringContainsString('textarea.remove()', $source);
        $this->assertStringContainsString('aria-hidden', $source);
    }
}
