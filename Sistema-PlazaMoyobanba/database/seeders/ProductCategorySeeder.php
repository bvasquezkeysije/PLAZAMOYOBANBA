<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $baseCategories = ['Bebidas', 'Higiene', 'Snacks'];

        foreach ($baseCategories as $name) {
            ProductCategory::updateOrCreate(
                ['name' => $name],
                ['code' => $this->codeFromName($name), 'active' => true]
            );
        }

        $productCategories = Product::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        foreach ($productCategories as $name) {
            ProductCategory::firstOrCreate(
                ['name' => $name],
                ['code' => $this->codeFromName($name), 'active' => true]
            );
        }
    }

    private function codeFromName(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'CAT', 0, 4));
        $suffix = strtoupper(substr(uniqid(), -3));

        return 'CAT-' . str_pad($base, 4, 'X') . '-' . $suffix;
    }
}

