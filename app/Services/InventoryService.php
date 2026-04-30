<?php

namespace App\Services;

use App\Enums\MovementReason;
use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\WorkOrderConsumption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Service InventoryService
 *
 * [SOLID - SRP] Servicio central que orquesta toda la lógica
 * de inventario: entradas, salidas, ajustes, devoluciones
 * y costeo promedio ponderado.
 *
 * [SOLID - OCP] Los tipos de movimiento se extienden vía Enums.
 * [SOLID - DIP] Se inyecta vía constructor / app() container.
 *
 * Estrategia de concurrencia:
 * SELECT FOR UPDATE + DB::transaction() para evitar race conditions.
 *
 * Triple validación anti-stock negativo:
 * 1. Form Request (UI)
 * 2. Service Layer (este archivo)
 * 3. DB Lock pesimista (SELECT FOR UPDATE)
 */
class InventoryService
{
    /* ================================================================== */
    /*  1. ENTRADAS DE MERCANCÍA (Compras)                                 */
    /* ================================================================== */

    /**
     * Registra una entrada de mercancía al inventario.
     * Recalcula el costo promedio ponderado.
     *
     * @param int         $variantId    ID de la variante
     * @param float       $quantity     Cantidad entrante
     * @param int|null    $supplierId   Proveedor
     * @param float|null  $unitCost     Costo unitario de esta compra
     * @param string|null $notes        Observaciones
     * @param string      $reason       Motivo (default: purchase)
     * @return InventoryMovement
     */
    public function registerEntry(
        int $variantId,
        float $quantity,
        ?int $supplierId = null,
        ?float $unitCost = null,
        ?string $notes = null,
        MovementReason $reason = MovementReason::PURCHASE,
    ): InventoryMovement {
        return DB::transaction(function () use ($variantId, $quantity, $supplierId, $unitCost, $notes, $reason) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            $stockBefore = (float) $variant->current_stock;
            $stockAfter  = $stockBefore + $quantity;

            // Recalcular costo promedio ponderado
            if ($unitCost !== null && $unitCost > 0) {
                $this->recalculateWeightedAverageCost($variant, $quantity, $unitCost);
            }

            // Crear movimiento Kardex
            $movement = InventoryMovement::create([
                'product_variant_id' => $variantId,
                'movement_type'      => MovementType::ENTRY,
                'quantity'           => $quantity,
                'stock_before'       => $stockBefore,
                'stock_after'        => $stockAfter,
                'supplier_id'        => $supplierId,
                'unit_cost'          => $unitCost,
                'reason'             => $reason,
                'notes'              => $notes,
                'performed_by'       => Auth::id(),
                'movement_date'      => now(),
            ]);

            // Actualizar stock atómico
            $variant->increment('current_stock', $quantity);

            return $movement;
        });
    }

    /* ================================================================== */
    /*  2. SALIDAS — Consumo por Orden de Trabajo                          */
    /* ================================================================== */

    /**
     * Descuenta insumos del inventario vinculados a una orden de trabajo.
     *
     * @throws InsufficientStockException Si el stock es insuficiente
     */
    public function consumeForWorkOrder(
        int $workOrderId,
        int $variantId,
        float $quantity,
        ?int $areaId = null,
        ?string $notes = null,
    ): WorkOrderConsumption {
        return DB::transaction(function () use ($workOrderId, $variantId, $quantity, $areaId, $notes) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            // Validación: No permitir stock negativo
            if ((float) $variant->current_stock < $quantity) {
                throw new InsufficientStockException(
                    sku: $variant->sku,
                    available: (float) $variant->current_stock,
                    requested: $quantity,
                );
            }

            $stockBefore = (float) $variant->current_stock;
            $stockAfter  = $stockBefore - $quantity;

            // Kardex
            $movement = InventoryMovement::create([
                'product_variant_id' => $variantId,
                'movement_type'      => MovementType::EXIT,
                'quantity'           => $quantity,
                'stock_before'       => $stockBefore,
                'stock_after'        => $stockAfter,
                'reference_type'     => 'work_order',
                'reference_id'       => $workOrderId,
                'unit_cost'          => $variant->cost_price,
                'reason'             => MovementReason::WORK_ORDER_CONSUMPTION,
                'notes'              => $notes,
                'performed_by'       => Auth::id(),
                'movement_date'      => now(),
            ]);

            // Consumo vinculado a la orden
            $consumption = WorkOrderConsumption::create([
                'work_order_id'         => $workOrderId,
                'product_variant_id'    => $variantId,
                'inventory_movement_id' => $movement->id,
                'quantity'              => $quantity,
                'area_id'               => $areaId,
                'performed_by'          => Auth::id(),
                'notes'                 => $notes,
            ]);

            // Decrementar stock atómico
            $variant->decrement('current_stock', $quantity);

            return $consumption;
        });
    }

    /* ================================================================== */
    /*  3. DEVOLUCIONES                                                     */
    /* ================================================================== */

    /**
     * Devuelve material al inventario desde una orden de trabajo.
     */
    public function returnFromWorkOrder(
        int $workOrderId,
        int $variantId,
        float $quantity,
        ?string $notes = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($workOrderId, $variantId, $quantity, $notes) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            $stockBefore = (float) $variant->current_stock;
            $stockAfter  = $stockBefore + $quantity;

            $movement = InventoryMovement::create([
                'product_variant_id' => $variantId,
                'movement_type'      => MovementType::RETURN,
                'quantity'           => $quantity,
                'stock_before'       => $stockBefore,
                'stock_after'        => $stockAfter,
                'reference_type'     => 'work_order',
                'reference_id'       => $workOrderId,
                'unit_cost'          => $variant->cost_price,
                'reason'             => MovementReason::RETURN_FROM_ORDER,
                'notes'              => $notes,
                'performed_by'       => Auth::id(),
                'movement_date'      => now(),
            ]);

            $variant->increment('current_stock', $quantity);

            return $movement;
        });
    }

    /* ================================================================== */
    /*  4. AJUSTES DE INVENTARIO                                           */
    /* ================================================================== */

    /**
     * Registra un ajuste de inventario (positivo o negativo).
     *
     * @throws InsufficientStockException Si el ajuste negativo excede el stock
     */
    public function registerAdjustment(
        int $variantId,
        float $quantity,
        bool $isPositive,
        MovementReason $reason,
        ?string $notes = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($variantId, $quantity, $isPositive, $reason, $notes) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);

            $stockBefore = (float) $variant->current_stock;

            if (! $isPositive && $stockBefore < $quantity) {
                throw new InsufficientStockException(
                    sku: $variant->sku,
                    available: $stockBefore,
                    requested: $quantity,
                );
            }

            $stockAfter = $isPositive
                ? $stockBefore + $quantity
                : $stockBefore - $quantity;

            $movement = InventoryMovement::create([
                'product_variant_id' => $variantId,
                'movement_type'      => MovementType::ADJUSTMENT,
                'quantity'           => $quantity,
                'stock_before'       => $stockBefore,
                'stock_after'        => $stockAfter,
                'reason'             => $reason,
                'notes'              => $notes,
                'performed_by'       => Auth::id(),
                'movement_date'      => now(),
            ]);

            if ($isPositive) {
                $variant->increment('current_stock', $quantity);
            } else {
                $variant->decrement('current_stock', $quantity);
            }

            return $movement;
        });
    }

    /* ================================================================== */
    /*  5. COSTEO PROMEDIO PONDERADO                                        */
    /* ================================================================== */

    /**
     * Recalcula el costo promedio ponderado tras una entrada.
     *
     * Fórmula:
     * nuevo_costo = (stock_actual × costo_actual + cantidad_nueva × costo_nuevo)
     *             / (stock_actual + cantidad_nueva)
     */
    private function recalculateWeightedAverageCost(
        ProductVariant $variant,
        float $newQuantity,
        float $newUnitCost,
    ): void {
        $currentStock = (float) $variant->current_stock;
        $currentCost  = (float) ($variant->cost_price ?? 0);

        if ($currentStock + $newQuantity > 0) {
            $weightedCost = (($currentStock * $currentCost) + ($newQuantity * $newUnitCost))
                          / ($currentStock + $newQuantity);

            $variant->update(['cost_price' => round($weightedCost, 2)]);
        }
    }

    /* ================================================================== */
    /*  6. CONSULTAS DE KARDEX                                             */
    /* ================================================================== */

    /**
     * Obtiene el Kardex completo de una variante.
     */
    public function getKardex(int $variantId, ?string $from = null, ?string $to = null)
    {
        $query = InventoryMovement::where('product_variant_id', $variantId)
            ->with(['performer', 'supplier'])
            ->orderByDesc('movement_date');

        if ($from) {
            $query->where('movement_date', '>=', $from);
        }
        if ($to) {
            $query->where('movement_date', '<=', $to);
        }

        return $query->get();
    }

    /**
     * Reconcilia el stock calculado vs el Kardex.
     * Útil para auditoría periódica.
     */
    public function reconcileStock(int $variantId): array
    {
        $variant = ProductVariant::findOrFail($variantId);

        $entries = InventoryMovement::where('product_variant_id', $variantId)
            ->whereIn('movement_type', [MovementType::ENTRY->value, MovementType::RETURN->value])
            ->sum('quantity');

        $exits = InventoryMovement::where('product_variant_id', $variantId)
            ->where('movement_type', MovementType::EXIT->value)
            ->sum('quantity');

        $adjustmentsAdd = InventoryMovement::where('product_variant_id', $variantId)
            ->where('movement_type', MovementType::ADJUSTMENT->value)
            ->where('reason', MovementReason::ADJUSTMENT_ADD->value)
            ->sum('quantity');

        $adjustmentsSub = InventoryMovement::where('product_variant_id', $variantId)
            ->where('movement_type', MovementType::ADJUSTMENT->value)
            ->whereIn('reason', [
                MovementReason::ADJUSTMENT_SUB->value,
                MovementReason::DAMAGE->value,
                MovementReason::EXPIRATION->value,
            ])
            ->sum('quantity');

        $calculatedStock = $entries - $exits + $adjustmentsAdd - $adjustmentsSub;

        return [
            'variant_id'       => $variantId,
            'sku'              => $variant->sku,
            'cached_stock'     => (float) $variant->current_stock,
            'calculated_stock' => $calculatedStock,
            'is_synced'        => abs((float) $variant->current_stock - $calculatedStock) < 0.01,
            'entries'          => $entries,
            'exits'            => $exits,
            'adjustments_add'  => $adjustmentsAdd,
            'adjustments_sub'  => $adjustmentsSub,
        ];
    }
}
