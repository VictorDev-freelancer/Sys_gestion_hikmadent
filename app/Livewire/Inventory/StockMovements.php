<?php

namespace App\Livewire\Inventory;

use App\Enums\MovementReason;
use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Services\InventoryService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire StockMovements
 *
 * [SOLID - SRP] Gestión de movimientos de inventario:
 * Entradas, salidas manuales, ajustes y consumos por OT.
 */
class StockMovements extends Component
{
    use WithPagination;

    /* ------------------------------------------------------------------ */
    /*  PROPIEDADES                                                        */
    /* ------------------------------------------------------------------ */

    public string $typeFilter = '';
    public string $search = '';

    // Modal: Nuevo movimiento
    public bool $showMovementModal = false;
    public string $movementAction = 'entry'; // entry, exit, adjustment, work_order
    public string $selectedVariantId = '';
    public string $movementQuantity = '';
    public string $movementSupplierId = '';
    public string $movementUnitCost = '';
    public string $movementNotes = '';
    public string $movementWorkOrderId = '';
    public string $movementAreaId = '';
    public bool $adjustmentIsPositive = true;
    public string $adjustmentReason = 'adjustment_add';

    /* ------------------------------------------------------------------ */
    /*  ACCIONES                                                           */
    /* ------------------------------------------------------------------ */

    public function openMovementModal(string $action = 'entry'): void
    {
        $this->resetMovementForm();
        $this->movementAction = $action;
        $this->showMovementModal = true;
    }

    public function saveMovement(): void
    {
        $this->validate([
            'selectedVariantId' => 'required|exists:product_variants,id',
            'movementQuantity'  => 'required|numeric|min:0.01',
        ]);

        $service = app(InventoryService::class);

        try {
            match ($this->movementAction) {
                'entry' => $service->registerEntry(
                    variantId: (int) $this->selectedVariantId,
                    quantity: (float) $this->movementQuantity,
                    supplierId: $this->movementSupplierId ? (int) $this->movementSupplierId : null,
                    unitCost: $this->movementUnitCost ? (float) $this->movementUnitCost : null,
                    notes: $this->movementNotes ?: null,
                ),
                'work_order' => $service->consumeForWorkOrder(
                    workOrderId: (int) $this->movementWorkOrderId,
                    variantId: (int) $this->selectedVariantId,
                    quantity: (float) $this->movementQuantity,
                    areaId: $this->movementAreaId ? (int) $this->movementAreaId : null,
                    notes: $this->movementNotes ?: null,
                ),
                'adjustment' => $service->registerAdjustment(
                    variantId: (int) $this->selectedVariantId,
                    quantity: (float) $this->movementQuantity,
                    isPositive: $this->adjustmentIsPositive,
                    reason: MovementReason::from($this->adjustmentReason),
                    notes: $this->movementNotes ?: null,
                ),
                'return' => $service->returnFromWorkOrder(
                    workOrderId: (int) $this->movementWorkOrderId,
                    variantId: (int) $this->selectedVariantId,
                    quantity: (float) $this->movementQuantity,
                    notes: $this->movementNotes ?: null,
                ),
            };

            $this->showMovementModal = false;
            $this->resetMovementForm();
            session()->flash('message', 'Movimiento registrado correctamente.');
        } catch (InsufficientStockException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    private function resetMovementForm(): void
    {
        $this->selectedVariantId = '';
        $this->movementQuantity = '';
        $this->movementSupplierId = '';
        $this->movementUnitCost = '';
        $this->movementNotes = '';
        $this->movementWorkOrderId = '';
        $this->movementAreaId = '';
        $this->adjustmentIsPositive = true;
        $this->adjustmentReason = 'adjustment_add';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /* ------------------------------------------------------------------ */
    /*  RENDER                                                             */
    /* ------------------------------------------------------------------ */

    public function render()
    {
        $movementsQuery = \App\Models\InventoryMovement::with(['productVariant.product', 'performer', 'supplier'])
            ->when($this->typeFilter, fn($q) => $q->where('movement_type', $this->typeFilter))
            ->when($this->search, function ($q) {
                $q->whereHas('productVariant', fn($v) => $v->where('sku', 'like', "%{$this->search}%")
                    ->orWhere('variant_name', 'like', "%{$this->search}%"));
            })
            ->orderByDesc('movement_date');

        return view('livewire.inventory.stock-movements', [
            'movements'      => $movementsQuery->paginate(20),
            'variants'       => ProductVariant::active()->with('product')->orderBy('sku')->get(),
            'suppliers'      => Supplier::active()->orderBy('name')->get(),
            'workOrders'     => WorkOrder::orderByDesc('created_at')->limit(50)->get(),
            'movementTypes'  => MovementType::options(),
            'adjustReasons'  => [
                'adjustment_add' => 'Ajuste positivo',
                'adjustment_sub' => 'Ajuste negativo',
                'damage'         => 'Daño / Merma',
                'expiration'     => 'Vencimiento',
            ],
        ])->layout('layouts.app');
    }
}
