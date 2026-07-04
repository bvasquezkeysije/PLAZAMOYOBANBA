<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('stock_min')) {
            $query->where('stock', '>=', (int) $request->stock_min);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->price_max);
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        if (Schema::hasTable('product_categories')) {
            $categories = ProductCategory::query()
                ->where('active', true)
                ->orderBy('name')
                ->pluck('name');

            $categoryRows = ProductCategory::query()
                ->when($request->filled('category_q'), function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->category_q.'%');
                })
                ->orderBy('name')
                ->get();
        } else {
            $categories = Product::query()
                ->select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');

            $categoryRows = collect();
        }

        return view('admin.modules.productos', compact('products', 'categories', 'categoryRows'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'product')
                ->withInput()
                ->with('open_create_product_modal', true);
        }

        $validated = $validator->validated();

        Product::create([
            'code' => $this->nextProductCode(),
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return back()
            ->with('product_success', 'Producto registrado correctamente.')
            ->with('open_create_product_modal', false);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'product')
                ->withInput()
                ->with('open_edit_product_modal', true)
                ->with('edit_product', [
                    'id' => $product->id,
                    'name' => $request->input('name', $product->name),
                    'category' => $request->input('category', $product->category),
                    'price' => $request->input('price', $product->price),
                    'stock' => $request->input('stock', $product->stock),
                    'active' => (string) $request->input('active', $product->active ? '1' : '0'),
                ]);
        }

        $product->update([
            'name' => $validator->validated()['name'],
            'category' => $validator->validated()['category'] ?? null,
            'price' => $validator->validated()['price'],
            'stock' => $validator->validated()['stock'],
            'active' => (bool) ($validator->validated()['active'] ?? true),
        ]);

        return back()->with('product_success', 'Producto actualizado correctamente.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update([
            'active' => ! $product->active,
        ]);

        return back()->with(
            'product_success',
            $product->active ? 'Producto activado correctamente.' : 'Producto desactivado correctamente.'
        );
    }

    public function storeCategory(Request $request)
    {
        if (!Schema::hasTable('product_categories')) {
            return back()
                ->with('category_success', 'Primero ejecuta migraciones para habilitar categorías.')
                ->with('open_categories_modal', true);
        }

        $validated = $request->validateWithBag('category', [
            'name' => ['required', 'string', 'max:100', 'unique:product_categories,name'],
        ]);

        ProductCategory::create([
            'code' => $this->nextCategoryCode(),
            'name' => $validated['name'],
            'active' => true,
        ]);

        return back()
            ->with('category_success', 'Categoría registrada correctamente.')
            ->with('open_categories_modal', true);
    }

    public function updateCategory(Request $request, ProductCategory $category)
    {
        if (!Schema::hasTable('product_categories')) {
            return back()
                ->with('category_success', 'Primero ejecuta migraciones para habilitar categorías.')
                ->with('open_categories_modal', true);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', 'unique:product_categories,name,'.$category->id],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'category')
                ->withInput()
                ->with('open_categories_modal', true)
                ->with('open_edit_category_modal', true)
                ->with('edit_category', [
                    'id' => $category->id,
                    'name' => $request->input('name', $category->name),
                ]);
        }

        $oldName = $category->name;
        $newName = $validator->validated()['name'];

        $category->update(['name' => $newName]);

        Product::where('category', $oldName)->update(['category' => $newName]);

        return back()
            ->with('category_success', 'Categoría actualizada correctamente.')
            ->with('open_categories_modal', true);
    }

    private function nextCategoryCode(): string
    {
        do {
            $code = 'CAT-' . strtoupper(substr(uniqid(), -4));
        } while (ProductCategory::where('code', $code)->exists());

        return $code;
    }

    private function nextProductCode(): string
    {
        do {
            $code = 'PRO-' . strtoupper(substr(uniqid(), -5));
        } while (Product::where('code', $code)->exists());

        return $code;
    }
}
