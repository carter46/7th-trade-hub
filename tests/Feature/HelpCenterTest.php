<?php

namespace Tests\Feature;

use App\Support\HelpContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_hub_loads_with_categories(): void
    {
        $this->get(route('help'))
            ->assertOk()
            ->assertSee('Help Center')
            ->assertSee('Getting Started')
            ->assertSee('Contact us');
    }

    public function test_help_article_loads(): void
    {
        $this->get(route('help.article', 'getting-started'))
            ->assertOk()
            ->assertSee('Getting Started with 7th Trade Hub')
            ->assertSee('Creating an account')
            ->assertSee('assets/images/ai-powered-device-concept copy.jpg', false);
    }

    public function test_every_help_article_has_an_existing_hero_image(): void
    {
        foreach (HelpContent::all() as $slug => $article) {
            $this->assertArrayHasKey('hero_image', $article, "Missing hero image for {$slug}.");
            $this->assertFileExists(public_path($article['hero_image']), "Hero image does not exist for {$slug}.");
        }
    }

    public function test_unknown_help_article_404s(): void
    {
        $this->get(route('help.article', 'does-not-exist'))
            ->assertNotFound();
    }

    public function test_help_content_estimates_reading_time(): void
    {
        $article = HelpContent::find('getting-started');
        $this->assertNotNull($article);
        $this->assertGreaterThanOrEqual(1, $article['reading_minutes']);
        $this->assertNotEmpty($article['updated_at_display']);
    }

    public function test_contact_page_loads(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Contact & Support')
            ->assertSee('Direct contact methods')
            ->assertSee('Go to Help Center')
            ->assertSee('Live chat is not enabled yet', false);
    }

    public function test_help_article_contains_section_anchors(): void
    {
        $this->get(route('help.article', 'billing-wallets-payments'))
            ->assertOk()
            ->assertSee('id="funding"', false)
            ->assertSee('data-help-section', false);
    }

    public function test_search_index_includes_guides_and_sections(): void
    {
        $index = HelpContent::searchIndex();
        $this->assertNotEmpty($index);
        $types = collect($index)->pluck('type')->unique()->all();
        $this->assertContains('guide', $types);
        $this->assertContains('section', $types);
    }
}
