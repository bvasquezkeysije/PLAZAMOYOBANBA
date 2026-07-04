<x-layouts.admin>
    <x-slot name="title">Productos</x-slot>

    <div
        x-data="{
            showFilters: false,
            showCategoriasModal: {{ (session('open_categories_modal') || $errors->category->any()) ? 'true' : 'false' }},
            showCreateCategoryModal: {{ old('category_form') === 'create' ? 'true' : 'false' }},
            showEditCategoryModal: {{ session('open_edit_category_modal') ? 'true' : 'false' }},
            showCreateProductModal: {{ (session('open_create_product_modal') || ($errors->product->any() && old('product_form') === 'create')) ? 'true' : 'false' }},
            showEditProductModal: {{ session('open_edit_product_modal') ? 'true' : 'false' }},
            showProductDetailModal: false,
            editCategoryId: {{ session('edit_category.id') ? (int) session('edit_category.id') : 'null' }},
            editCategoryName: @js((string) session('edit_category.name', '')),
            editProductId: {{ session('edit_product.id') ? (int) session('edit_product.id') : 'null' }},
            editProductName: @js((string) session('edit_product.name', '')),
            editProductCategory: @js((string) session('edit_product.category', '')),
            editProductPrice: @js((string) session('edit_product.price', '')),
            editProductStock: @js((string) session('edit_product.stock', '')),
            editProductActive: @js((string) session('edit_product.active', '1')),
            detailProduct: null,
            categorySearch: @js((string) request('category_q', '')),
            openEditCategory(category) {
                this.editCategoryId = category.id;
                this.editCategoryName = category.name;
                this.showEditCategoryModal = true;
            },
            openEditProduct(product) {
                this.editProductId = product.id;
                this.editProductName = product.name;
                this.editProductCategory = product.category ?? '';
                this.editProductPrice = product.price;
                this.editProductStock = product.stock;
                this.editProductActive = product.active ? '1' : '0';
                this.showEditProductModal = true;
            },
            openProductDetail(product) {
                this.detailProduct = product;
                this.showProductDetailModal = true;
            }
        }"
        class="space-y-4"
    >
        @if (session('product_success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('product_success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.productos') }}" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <input type="text" name="q" value="{{ request('q') }}" class="w-full lg:flex-1 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar producto por nombre, código o categoría">

                    <button type="button" @click="showFilters = !showFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50" aria-label="Filtros" title="Filtros">
                        <img src="{{ asset('images/flitro.svg') }}" alt="Filtros" class="w-4 h-4">
                    </button>
                    <button type="button" @click="showCreateProductModal = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-semibold hover:bg-emerald-100" aria-label="Añadir producto" title="Añadir producto">
                        <img src="{{ asset('images/agregar-productos.svg') }}" alt="Añadir producto" class="w-4 h-4">
                    </button>
                    <button type="button" @click="showCategoriasModal = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-sm font-semibold hover:bg-blue-100" aria-label="Categorías" title="Categorías">
                        <img src="{{ asset('images/categoria.svg') }}" alt="Categorías" class="w-4 h-4">
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800" aria-label="Buscar" title="Buscar">
                        <img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert">
                    </button>
                </div>

                <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4" style="display: none;">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría</label>
                        <select name="category" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Stock mín.</label><input type="number" name="stock_min" value="{{ request('stock_min') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="0"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Precio mín.</label><input type="number" step="0.01" name="price_min" value="{{ request('price_min') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="0.00"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Precio máx.</label><input type="number" step="0.01" name="price_max" value="{{ request('price_max') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="0.00"></div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left"><tr><th class="px-4 py-3">Código</th><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Categoría</th><th class="px-4 py-3">Precio</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-semibold">{{ $product->code }}</td>
                                <td class="px-4 py-3">{{ $product->name }}</td>
                                <td class="px-4 py-3">{{ $product->category ?? '-' }}</td>
                                <td class="px-4 py-3">S/ {{ number_format($product->price, 2) }}</td>
                                <td class="px-4 py-3">{{ $product->stock }}</td>
                                <td class="px-4 py-3">{{ $product->active ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            x-on:click="openProductDetail({{ \Illuminate\Support\Js::from(['id' => $product->id, 'code' => $product->code, 'name' => $product->name, 'category' => $product->category, 'price' => (float) $product->price, 'stock' => (int) $product->stock, 'active' => (bool) $product->active, 'created_at' => optional($product->created_at)->format('d/m/Y H:i')]) }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-slate-600 text-white text-xs font-semibold hover:bg-slate-700"
                                            aria-label="Ver detalle"
                                            title="Ver detalle"
                                        >
                                            <img src="{{ asset('images/ver-detalle.svg') }}" alt="Ver detalle" class="w-4 h-4 brightness-0 invert">
                                        </button>
                                        <button
                                            type="button"
                                            x-on:click="openEditProduct({{ \Illuminate\Support\Js::from(['id' => $product->id, 'name' => $product->name, 'category' => $product->category, 'price' => (string) $product->price, 'stock' => (int) $product->stock, 'active' => (bool) $product->active]) }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                            aria-label="Editar"
                                            title="Editar"
                                        >
                                            <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                        </button>
                                        <form action="{{ route('admin.productos.toggle-status', $product) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-white text-xs font-semibold {{ $product->active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                                                aria-label="{{ $product->active ? 'Desactivar' : 'Activar' }}"
                                                title="{{ $product->active ? 'Desactivar' : 'Activar' }}"
                                            >
                                                <img src="{{ asset('images/eliminar-descativar.svg') }}" alt="{{ $product->active ? 'Desactivar' : 'Activar' }}" class="w-4 h-4 brightness-0 invert">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t"><td class="px-4 py-3" colspan="7">Sin datos por ahora.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $products->links() }}</div>

        <div x-cloak x-show="showCreateProductModal" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showCreateProductModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Añadir producto</h3>
                    <button type="button" @click="showCreateProductModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.productos.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="product_form" value="create">
                    @if ($errors->product->any() && old('product_form') === 'create')
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->product->first() }}
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Categoría</label>
                            <input list="product-categories-list" type="text" name="category" value="{{ old('category') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Precio</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Stock</label>
                            <input type="number" min="0" name="stock" value="{{ old('stock') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                            <select name="active" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="1" {{ old('active', '1') === '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('active') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">Guardar producto</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showEditProductModal" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showEditProductModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar producto</h3>
                    <button type="button" @click="showEditProductModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="'{{ route('admin.productos.update', ['product' => '__ID__']) }}'.replace('__ID__', editProductId ?? '')" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="product_form" value="edit">
                    @if ($errors->product->any() && old('product_form') === 'edit')
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->product->first() }}
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                            <input type="text" name="name" x-model="editProductName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Categoría</label>
                            <input list="product-categories-list" type="text" name="category" x-model="editProductCategory" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Precio</label>
                            <input type="number" step="0.01" min="0" name="price" x-model="editProductPrice" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Stock</label>
                            <input type="number" min="0" name="stock" x-model="editProductStock" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                            <select name="active" x-model="editProductActive" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showProductDetailModal" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showProductDetailModal = false"></div>
            <div class="relative w-full max-w-xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Detalle de producto</h3>
                    <button type="button" @click="showProductDetailModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <div class="p-6 space-y-3 text-sm text-slate-700" x-show="detailProduct">
                    <div><span class="font-semibold">Código:</span> <span x-text="detailProduct?.code"></span></div>
                    <div><span class="font-semibold">Nombre:</span> <span x-text="detailProduct?.name"></span></div>
                    <div><span class="font-semibold">Categoría:</span> <span x-text="detailProduct?.category || '-'"></span></div>
                    <div><span class="font-semibold">Precio:</span> S/ <span x-text="Number(detailProduct?.price ?? 0).toFixed(2)"></span></div>
                    <div><span class="font-semibold">Stock:</span> <span x-text="detailProduct?.stock"></span></div>
                    <div><span class="font-semibold">Estado:</span> <span x-text="detailProduct?.active ? 'Activo' : 'Inactivo'"></span></div>
                    <div><span class="font-semibold">Creado:</span> <span x-text="detailProduct?.created_at || '-'"></span></div>
                </div>
            </div>
        </div>

        <datalist id="product-categories-list">
            @foreach ($categories as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>

        <div x-cloak x-show="showCategoriasModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="showCategoriasModal = false"></div>
            <div class="relative w-full max-w-3xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Gestión de categorías</h3>
                    <button type="button" @click="showCategoriasModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    @if (session('category_success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('category_success') }}
                        </div>
                    @endif
                    @if ($errors->category->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->category->first() }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <input
                            type="text"
                            x-model.debounce.150ms="categorySearch"
                            placeholder="Buscar categoría por nombre"
                            class="md:col-span-8 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                        <div class="md:col-span-4 flex gap-2">
                            <button type="button" @click="showCreateCategoryModal = true" class="flex-1 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">Añadir categoría</button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left">
                                <tr>
                                    <th class="px-4 py-3">Código</th>
                                    <th class="px-4 py-3">Categoría</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categoryRows as $categoryRow)
                                    <tr
                                        class="border-t"
                                        x-show="'{{ \Illuminate\Support\Str::lower($categoryRow->name) }}'.includes(categorySearch.toLowerCase())"
                                    >
                                        <td class="px-4 py-3 font-semibold">{{ $categoryRow->code }}</td>
                                        <td class="px-4 py-3">{{ $categoryRow->name }}</td>
                                        <td class="px-4 py-3">
                                            <button
                                                type="button"
                                                x-on:click="openEditCategory({{ \Illuminate\Support\Js::from(['id' => $categoryRow->id, 'name' => $categoryRow->name]) }})"
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                                aria-label="Editar"
                                                title="Editar"
                                            >
                                                <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-slate-500">No hay categorías registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showCreateCategoryModal" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showCreateCategoryModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Añadir categoría</h3>
                    <button type="button" @click="showCreateCategoryModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.productos.categories.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="category_form" value="create">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre de categoría</label>
                        <input type="text" name="name" value="{{ old('category_form') === 'create' ? old('name') : '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Ej: Bebidas" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">Guardar categoría</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showEditCategoryModal" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showEditCategoryModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar categoría</h3>
                    <button type="button" @click="showEditCategoryModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="'{{ route('admin.productos.categories.update', ['category' => '__ID__']) }}'.replace('__ID__', editCategoryId ?? '')" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre de categoría</label>
                        <input type="text" name="name" x-model="editCategoryName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
