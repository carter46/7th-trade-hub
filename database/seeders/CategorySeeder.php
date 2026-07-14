<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Web Services', 'slug' => 'web-services'],
            ['name' => 'Digital Products', 'slug' => 'digital-products'],
            ['name' => 'Templates', 'slug' => 'templates'],
            ['name' => 'Code', 'slug' => 'code'],
            ['name' => 'Documents', 'slug' => 'documents'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'type' => 'marketplace', 'is_active' => true]
            );
        }
    }
}
