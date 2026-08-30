<?php

namespace Tests\Feature\Catalog;

use App\Models\ProductType;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DocumentsReceiptSplitTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_creates_separate_receipt_and_documents_services(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        Artisan::call('catalog:backfill-hierarchy');

        $category = ServiceCategory::query()->where('slug', 'business-documents')->firstOrFail();

        $receipt = ProductType::query()->where('slug', 'receipt')->firstOrFail();
        $document = ProductType::query()->where('slug', 'document')->firstOrFail();

        $this->assertSame($category->id, $receipt->service_category_id);
        $this->assertSame($category->id, $document->service_category_id);
        $this->assertSame('Receipt', $receipt->name);
        $this->assertSame('Documents', $document->name);
        $this->assertDatabaseMissing('product_types', ['slug' => 'document_template']);
    }

    public function test_business_documents_category_lists_both_services(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        Artisan::call('catalog:backfill-hierarchy');

        $this->get(route('services.segment', 'business-documents'))
            ->assertOk()
            ->assertSee('Receipt')
            ->assertSee('Documents')
            ->assertDontSee('Document Templates');
    }

    public function test_legacy_templates_url_redirects_to_receipt_service(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        Artisan::call('catalog:backfill-hierarchy');

        $this->get('/templates')
            ->assertRedirect(route('services.type', [
                'category' => 'business-documents',
                'service' => 'receipt',
            ]));
    }

    public function test_legacy_templates_product_url_redirects_to_canonical_product_page(): void
    {
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        Artisan::call('catalog:backfill-hierarchy');

        $this->get('/templates/invoice-receipt-set')
            ->assertRedirect(route('services.nested.show', [
                'category' => 'business-documents',
                'service' => 'receipt',
                'productSlug' => 'invoice-receipt-set',
            ]));
    }

    public function test_document_templates_legacy_path_redirects_to_receipt_service(): void
    {
        $this->get('/document-templates')
            ->assertRedirect('/services/business-documents/receipt');
    }
}
