<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire ProductManagement
 *
 * [SOLID - SRP] CRUD completo de productos, variantes,
 * categorías y proveedores.
 * Accesible solo para Super usuario y Administración.
 */
class ProductManagement extends Component
{
    use WithPagination;

    /* ------------------------------------------------------------------ */
    /*  PROPIEDADES                                                        */
    /* ------------------------------------------------------------------ */

    public string $search = '';
    public string $categoryFilter = '';
    public string $activeTab = 'products'; // products, categories, suppliers

    // Modal: Categoría
    public bool $showCategoryModal = false;
    public ?int $editingCategoryId = null;
    public string $categoryName = '';
    public string $categoryDescription = '';

    // Modal: Proveedor
    public bool $showSupplierModal = false;
    public ?int $editingSupplierId = null;
    public string $supplierName = '';
    public string $supplierContact = '';
    public string $supplierPhone = '';
    public string $supplierEmail = '';
    public string $supplierAddress = '';

    // Modal: Producto
    public bool $showProductModal = false;
    public ?int $editingProductId = null;
    public string $productName = '';
    public string $productDescription = '';
    public string $productCategoryId = '';
    public string $productUnitOfMeasure = 'unidad';
    public string $productMinimumStock = '0';

    // Modal: Variante
    public bool $showVariantModal = false;
    public ?int $editingVariantId = null;
    public string $variantProductId = '';
    public string $variantName = '';
    public string $variantSku = '';
    public string $variantColor = '';
    public string $variantSize = '';
    public string $variantMinimumStock = '';
    public string $variantCostPrice = '';
    public string $variantExpiresAt = '';

    /* ------------------------------------------------------------------ */
    /*  CATEGORÍAS CRUD                                                    */
    /* ------------------------------------------------------------------ */

    public function openCategoryModal(?int $id = null): void
    {
        $this->resetCategoryForm();
        if ($id) {
            $cat = Category::findOrFail($id);
            $this->editingCategoryId = $id;
            $this->categoryName = $cat->name;
            $this->categoryDescription = $cat->description ?? '';
        }
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => 'required|string|max:100',
        ]);

        Category::updateOrCreate(
            ['id' => $this->editingCategoryId],
            [
                'name'        => $this->categoryName,
                'slug'        => Str::slug($this->categoryName),
                'description' => $this->categoryDescription ?: null,
            ]
        );

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        session()->flash('message', 'Categoría guardada correctamente.');
    }

    public function deleteCategory(int $id): void
    {
        $cat = Category::findOrFail($id);
        if ($cat->products()->exists()) {
            session()->flash('error', 'No se puede eliminar una categoría con productos asociados.');
            return;
        }
        $cat->delete();
        session()->flash('message', 'Categoría eliminada.');
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
    }

    /* ------------------------------------------------------------------ */
    /*  PROVEEDORES CRUD                                                   */
    /* ------------------------------------------------------------------ */

    public function openSupplierModal(?int $id = null): void
    {
        $this->resetSupplierForm();
        if ($id) {
            $sup = Supplier::findOrFail($id);
            $this->editingSupplierId = $id;
            $this->supplierName = $sup->name;
            $this->supplierContact = $sup->contact_name ?? '';
            $this->supplierPhone = $sup->phone ?? '';
            $this->supplierEmail = $sup->email ?? '';
            $this->supplierAddress = $sup->address ?? '';
        }
        $this->showSupplierModal = true;
    }

    public function saveSupplier(): void
    {
        $this->validate([
            'supplierName' => 'required|string|max:150',
            'supplierEmail' => 'nullable|email',
        ]);

        Supplier::updateOrCreate(
            ['id' => $this->editingSupplierId],
            [
                'name'         => $this->supplierName,
                'contact_name' => $this->supplierContact ?: null,
                'phone'        => $this->supplierPhone ?: null,
                'email'        => $this->supplierEmail ?: null,
                'address'      => $this->supplierAddress ?: null,
            ]
        );

        $this->showSupplierModal = false;
        $this->resetSupplierForm();
        session()->flash('message', 'Proveedor guardado correctamente.');
    }

    public function deleteSupplier(int $id): void
    {
        $sup = Supplier::findOrFail($id);
        if ($sup->inventoryMovements()->exists()) {
            session()->flash('error', 'No se puede eliminar un proveedor con movimientos asociados.');
            return;
        }
        $sup->delete();
        session()->flash('message', 'Proveedor eliminado.');
    }

    private function resetSupplierForm(): void
    {
        $this->editingSupplierId = null;
        $this->supplierName = '';
        $this->supplierContact = '';
        $this->supplierPhone = '';
        $this->supplierEmail = '';
        $this->supplierAddress = '';
    }

    /* ------------------------------------------------------------------ */
    /*  PRODUCTOS CRUD                                                     */
    /* ------------------------------------------------------------------ */

    public function openProductModal(?int $id = null): void
    {
        $this->resetProductForm();
        if ($id) {
            $prod = Product::findOrFail($id);
            $this->editingProductId = $id;
            $this->productName = $prod->name;
            $this->productDescription = $prod->description ?? '';
            $this->productCategoryId = (string) $prod->category_id;
            $this->productUnitOfMeasure = $prod->unit_of_measure;
            $this->productMinimumStock = (string) $prod->minimum_stock;
        }
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $this->validate([
            'productName'          => 'required|string|max:150',
            'productCategoryId'    => 'required|exists:categories,id',
            'productUnitOfMeasure' => 'required|string|max:20',
            'productMinimumStock'  => 'required|numeric|min:0',
        ]);

        Product::updateOrCreate(
            ['id' => $this->editingProductId],
            [
                'name'            => $this->productName,
                'description'     => $this->productDescription ?: null,
                'category_id'     => (int) $this->productCategoryId,
                'unit_of_measure' => $this->productUnitOfMeasure,
                'minimum_stock'   => (float) $this->productMinimumStock,
            ]
        );

        $this->showProductModal = false;
        $this->resetProductForm();
        session()->flash('message', 'Producto guardado correctamente.');
    }

    public function deleteProduct(int $id): void
    {
        $prod = Product::findOrFail($id);
        if ($prod->variants()->where('current_stock', '>', 0)->exists()) {
            session()->flash('error', 'No se puede eliminar un producto con stock activo.');
            return;
        }
        $prod->delete();
        session()->flash('message', 'Producto eliminado.');
    }

    private function resetProductForm(): void
    {
        $this->editingProductId = null;
        $this->productName = '';
        $this->productDescription = '';
        $this->productCategoryId = '';
        $this->productUnitOfMeasure = 'unidad';
        $this->productMinimumStock = '0';
    }

    /* ------------------------------------------------------------------ */
    /*  VARIANTES CRUD                                                     */
    /* ------------------------------------------------------------------ */

    public function openVariantModal(?int $id = null, ?int $productId = null): void
    {
        $this->resetVariantForm();
        if ($id) {
            $var = ProductVariant::findOrFail($id);
            $this->editingVariantId = $id;
            $this->variantProductId = (string) $var->product_id;
            $this->variantName = $var->variant_name;
            $this->variantSku = $var->sku;
            $this->variantColor = $var->color ?? '';
            $this->variantSize = $var->size ?? '';
            $this->variantMinimumStock = $var->minimum_stock !== null ? (string) $var->minimum_stock : '';
            $this->variantCostPrice = $var->cost_price !== null ? (string) $var->cost_price : '';
            $this->variantExpiresAt = $var->expires_at?->format('Y-m-d') ?? '';
        } elseif ($productId) {
            $this->variantProductId = (string) $productId;
        }
        $this->showVariantModal = true;
    }

    public function saveVariant(): void
    {
        $this->validate([
            'variantProductId'    => 'required|exists:products,id',
            'variantName'         => 'required|string|max:100',
            'variantSku'          => 'required|string|max:50|unique:product_variants,sku,' . ($this->editingVariantId ?? 'NULL'),
            'variantMinimumStock' => 'nullable|numeric|min:0',
            'variantCostPrice'    => 'nullable|numeric|min:0',
            'variantExpiresAt'    => 'nullable|date',
        ]);

        ProductVariant::updateOrCreate(
            ['id' => $this->editingVariantId],
            [
                'product_id'    => (int) $this->variantProductId,
                'variant_name'  => $this->variantName,
                'sku'           => $this->variantSku,
                'color'         => $this->variantColor ?: null,
                'size'          => $this->variantSize ?: null,
                'minimum_stock' => $this->variantMinimumStock !== '' ? (float) $this->variantMinimumStock : null,
                'cost_price'    => $this->variantCostPrice !== '' ? (float) $this->variantCostPrice : null,
                'expires_at'    => $this->variantExpiresAt ?: null,
            ]
        );

        $this->showVariantModal = false;
        $this->resetVariantForm();
        session()->flash('message', 'Variante guardada correctamente.');
    }

    public function deleteVariant(int $id): void
    {
        $var = ProductVariant::findOrFail($id);
        if ((float) $var->current_stock > 0) {
            session()->flash('error', 'No se puede eliminar una variante con stock activo.');
            return;
        }
        $var->delete();
        session()->flash('message', 'Variante eliminada.');
    }

    private function resetVariantForm(): void
    {
        $this->editingVariantId = null;
        $this->variantProductId = '';
        $this->variantName = '';
        $this->variantSku = '';
        $this->variantColor = '';
        $this->variantSize = '';
        $this->variantMinimumStock = '';
        $this->variantCostPrice = '';
        $this->variantExpiresAt = '';
    }

    /* ------------------------------------------------------------------ */
    /*  RENDER                                                             */
    /* ------------------------------------------------------------------ */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $productsQuery = Product::with(['category', 'variants'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter));

        return view('livewire.inventory.product-management', [
            'products'   => $productsQuery->paginate(15),
            'categories' => Category::active()->orderBy('name')->get(),
            'suppliers'  => Supplier::active()->orderBy('name')->get(),
            'allProducts' => Product::active()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
