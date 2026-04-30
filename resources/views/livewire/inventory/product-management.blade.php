<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🏷️ {{ __('Gestión de Productos e Insumos') }}
            </h2>
            <a href="{{ route('inventory.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium" wire:navigate>← Volver al Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow">{{ session('message') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow">{{ session('error') }}</div>
            @endif

            {{-- Tabs --}}
            <div class="flex gap-2 border-b border-gray-200 pb-2">
                <button wire:click="$set('activeTab', 'products')" class="px-4 py-2 rounded-t-lg font-bold text-sm transition {{ $activeTab === 'products' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">📦 Productos</button>
                <button wire:click="$set('activeTab', 'categories')" class="px-4 py-2 rounded-t-lg font-bold text-sm transition {{ $activeTab === 'categories' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">📂 Categorías</button>
                <button wire:click="$set('activeTab', 'suppliers')" class="px-4 py-2 rounded-t-lg font-bold text-sm transition {{ $activeTab === 'suppliers' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">🏭 Proveedores</button>
            </div>

            {{-- TAB: PRODUCTOS --}}
            @if($activeTab === 'products')
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 flex justify-between items-center border-b">
                    <div class="flex items-center gap-4">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto..." class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64">
                        <select wire:model.live="categoryFilter" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="openProductModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition">+ Nuevo Producto</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Categoría</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Variantes</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Stock Total</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($products as $prod)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-mono text-sm text-indigo-600 font-bold">{{ $prod->code }}</td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $prod->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $prod->unit_of_measure }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $prod->category->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $prod->variants->count() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-sm text-gray-900">{{ number_format($prod->variants->sum('current_stock'), 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <button wire:click="openVariantModal(null, {{ $prod->id }})" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded hover:bg-emerald-200 transition font-bold">+ Variante</button>
                                            <button wire:click="openProductModal({{ $prod->id }})" class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded hover:bg-yellow-200 transition font-bold">Editar</button>
                                            <button wire:click="deleteProduct({{ $prod->id }})" wire:confirm="¿Eliminar este producto?" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200 transition font-bold">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                                {{-- Variantes expandidas --}}
                                @foreach($prod->variants as $var)
                                    <tr class="bg-gray-50/50">
                                        <td class="px-4 py-2 pl-10 font-mono text-xs text-gray-500">└ {{ $var->sku }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-600">
                                            {{ $var->variant_name }}
                                            @if($var->color) <span class="text-gray-400">| {{ $var->color }}</span> @endif
                                            @if($var->size) <span class="text-gray-400">| {{ $var->size }}</span> @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-400">
                                            @if($var->expires_at)
                                                <span class="{{ $var->is_expired ? 'text-red-600 font-bold' : 'text-amber-600' }}">
                                                    Vence: {{ $var->expires_at->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-400">
                                            @if($var->cost_price) S/ {{ number_format($var->cost_price, 2) }} @else — @endif
                                        </td>
                                        <td class="px-4 py-2 text-right font-bold text-sm {{ $var->is_low_stock ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $var->current_stock }}
                                            @if($var->is_low_stock) <span class="text-xs text-red-400">⚠️</span> @endif
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <div class="flex justify-center gap-1">
                                                <button wire:click="openVariantModal({{ $var->id }})" class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded hover:bg-yellow-200 transition font-bold">Editar</button>
                                                <button wire:click="deleteVariant({{ $var->id }})" wire:confirm="¿Eliminar esta variante?" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200 transition font-bold">Eliminar</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No hay productos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $products->links() }}</div>
            </div>
            @endif

            {{-- TAB: CATEGORÍAS --}}
            @if($activeTab === 'categories')
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 flex justify-between items-center border-b">
                    <h3 class="font-bold text-gray-800">Categorías de Productos</h3>
                    <button wire:click="openCategoryModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition">+ Nueva Categoría</button>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($categories as $cat)
                        <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition">
                            <div>
                                <p class="font-medium text-gray-900">{{ $cat->name }}</p>
                                <p class="text-sm text-gray-400">{{ $cat->description ?? 'Sin descripción' }} · {{ $cat->products_count ?? 0 }} productos</p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="openCategoryModal({{ $cat->id }})" class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200 transition font-bold">Editar</button>
                                <button wire:click="deleteCategory({{ $cat->id }})" wire:confirm="¿Eliminar esta categoría?" class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition font-bold">Eliminar</button>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400">No hay categorías registradas.</div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- TAB: PROVEEDORES --}}
            @if($activeTab === 'suppliers')
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 flex justify-between items-center border-b">
                    <h3 class="font-bold text-gray-800">Proveedores</h3>
                    <button wire:click="openSupplierModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition">+ Nuevo Proveedor</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contacto</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Teléfono</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($suppliers as $sup)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $sup->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $sup->contact_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $sup->phone ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $sup->email ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <button wire:click="openSupplierModal({{ $sup->id }})" class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded hover:bg-yellow-200 transition font-bold">Editar</button>
                                            <button wire:click="deleteSupplier({{ $sup->id }})" wire:confirm="¿Eliminar este proveedor?" class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition font-bold">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay proveedores registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- MODAL: Categoría --}}
    @if($showCategoryModal)
    <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingCategoryId ? 'Editar' : 'Nueva' }} Categoría</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" wire:model="categoryName" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('categoryName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea wire:model="categoryDescription" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showCategoryModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                <button wire:click="saveCategory" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Guardar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Proveedor --}}
    @if($showSupplierModal)
    <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingSupplierId ? 'Editar' : 'Nuevo' }} Proveedor</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" wire:model="supplierName" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('supplierName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contacto</label>
                    <input type="text" wire:model="supplierContact" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" wire:model="supplierPhone" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="supplierEmail" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('supplierEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <textarea wire:model="supplierAddress" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showSupplierModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                <button wire:click="saveSupplier" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Guardar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Producto --}}
    @if($showProductModal)
    <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingProductId ? 'Editar' : 'Nuevo' }} Producto</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" wire:model="productName" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('productName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                    <select wire:model="productCategoryId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccionar...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('productCategoryId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida</label>
                        <select wire:model="productUnitOfMeasure" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="unidad">Unidad</option>
                            <option value="caja">Caja</option>
                            <option value="ml">ml</option>
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="litro">Litro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                        <input type="number" step="0.01" min="0" wire:model="productMinimumStock" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea wire:model="productDescription" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showProductModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                <button wire:click="saveProduct" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Guardar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Variante --}}
    @if($showVariantModal)
    <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingVariantId ? 'Editar' : 'Nueva' }} Variante</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto *</label>
                    <select wire:model="variantProductId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccionar...</option>
                        @foreach($allProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('variantProductId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Variante *</label>
                        <input type="text" wire:model="variantName" placeholder="Ej: A1 #18" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @error('variantName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                        <input type="text" wire:model="variantSku" placeholder="Ej: ZRC-A1-18" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @error('variantSku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input type="text" wire:model="variantColor" placeholder="Ej: A1" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tamaño</label>
                        <input type="text" wire:model="variantSize" placeholder="Ej: #18" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                        <input type="number" step="0.01" min="0" wire:model="variantMinimumStock" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Costo Unit. (S/)</label>
                        <input type="number" step="0.01" min="0" wire:model="variantCostPrice" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vencimiento</label>
                        <input type="date" wire:model="variantExpiresAt" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showVariantModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                <button wire:click="saveVariant" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
