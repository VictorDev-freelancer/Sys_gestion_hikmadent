<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Livewire\Component;

/**
 * Livewire KardexReport
 *
 * [SOLID - SRP] Visualización del Kardex (historial de movimientos)
 * por variante, con filtros de fecha y reconciliación de stock.
 */
class KardexReport extends Component
{
    public string $selectedVariantId = '';
    public string $selectedProductId = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public ?array $reconciliation = null;

    public function updatedSelectedProductId(): void
    {
        $this->selectedVariantId = '';
        $this->reconciliation = null;
    }

    public function reconcile(): void
    {
        if (! $this->selectedVariantId) {
            return;
        }

        $service = app(InventoryService::class);
        $this->reconciliation = $service->reconcileStock((int) $this->selectedVariantId);
    }

    public function render()
    {
        $movements = collect();
        $selectedVariant = null;

        if ($this->selectedVariantId) {
            $selectedVariant = ProductVariant::with('product')->find($this->selectedVariantId);

            $query = InventoryMovement::where('product_variant_id', $this->selectedVariantId)
                ->with(['performer', 'supplier'])
                ->orderByDesc('movement_date');

            if ($this->dateFrom) {
                $query->where('movement_date', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $query->where('movement_date', '<=', $this->dateTo . ' 23:59:59');
            }

            $movements = $query->get();
        }

        $products = Product::active()->orderBy('name')->get();
        $variants = collect();
        if ($this->selectedProductId) {
            $variants = ProductVariant::where('product_id', $this->selectedProductId)
                ->active()
                ->orderBy('variant_name')
                ->get();
        }

        return view('livewire.inventory.kardex-report', [
            'movements'       => $movements,
            'products'        => $products,
            'variants'        => $variants,
            'selectedVariant' => $selectedVariant,
        ])->layout('layouts.app');
    }
}
