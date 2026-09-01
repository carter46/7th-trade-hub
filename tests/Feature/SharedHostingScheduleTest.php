<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SharedHostingScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_console_schedule_uses_in_process_artisan_calls(): void
    {
        $contents = File::get(base_path('routes/console.php'));

        $this->assertStringNotContainsString('Schedule::command(', $contents);
        $this->assertStringContainsString('Artisan::call', $contents);
        $this->assertStringContainsString('Schedule::call', $contents);
    }
}
