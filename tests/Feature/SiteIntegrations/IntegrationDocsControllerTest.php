<?php

namespace Tests\Feature\SiteIntegrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationDocsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_integration_docs_index_renders_readme(): void
    {
        $response = $this->get(route('developers.integrations.index'));

        $response->assertOk();
        $response->assertSee('Site Integration Platform', false);
        $response->assertSee('Merchant guide', false);
    }

    public function test_integration_docs_show_merchant_guide(): void
    {
        $response = $this->get(route('developers.integrations.show', ['path' => 'MERCHANT-GUIDE']));

        $response->assertOk();
        $response->assertSee('integrate your website with 7th Trade Hub', false);
        $response->assertSee('Exact paths and routing', false);
    }

    public function test_integration_docs_show_nested_checklist(): void
    {
        $response = $this->get(route('developers.integrations.show', ['path' => 'checklists/MERCHANT-GO-LIVE']));

        $response->assertOk();
        $response->assertSee('Merchant go-live checklist', false);
    }

    public function test_integration_docs_show_php_sample_as_code(): void
    {
        $response = $this->get(route('developers.integrations.show', ['path' => 'samples/php/consume-validate.php']));

        $response->assertOk();
        $response->assertSee('seventh_tradehub_validate_token', false);
    }

    public function test_samples_directory_redirects_to_index(): void
    {
        $response = $this->get('/developers/integrations/samples');

        $response->assertRedirect('/developers/integrations/samples/README');
    }

    public function test_integration_docs_rejects_path_traversal(): void
    {
        $response = $this->get('/developers/integrations/../../.env');

        $response->assertNotFound();
    }

    public function test_integration_docs_download_sample_file(): void
    {
        $response = $this->get(route('developers.integrations.download', ['path' => 'samples/php/consume-validate.php']));

        $response->assertOk();
        $response->assertDownload('consume-validate.php');
    }
}
