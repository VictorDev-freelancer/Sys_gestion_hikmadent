<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Livewire\Component;

/**
 * Livewire InventoryDashboard
 *
 * [SOLID - SRP] Dashboard principal del módulo de inventario.
 * Muestra KPIs, alertas de stock bajo, productos próximos a vencer,
 * y resumen de movimientos recientes.
 */
class InventoryDashboard extends Component
{
    public function render()
    {
        $totalProducts = ProductVariant::active()->count();
        $lowStockCount = ProductVariant::active()
            ->whereNotNull('minimum_stock')
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();
        $expiringCount = ProductVariant::active()->expiringSoon(30)->count();
        $totalValue    = ProductVariant::active()
            ->selectRaw('SUM(current_stock * COALESCE(cost_price, 0)) as total')
            ->value('total') ?? 0;

        $lowStockItems = ProductVariant::active()
            ->whereNotNull('minimum_stock')
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->with('product.category')
            ->limit(10)
            ->get();

        $expiringItems = ProductVariant::active()
            ->expiringSoon(30)
            ->with('product.category')
            ->orderBy('expires_at')
            ->limit(10)
            ->get();

        $recentMovements = InventoryMovement::with(['productVariant.product', 'performer'])
            ->orderByDesc('movement_date')
            ->limit(15)
            ->get();

        $categories = Category::active()
            ->withCount('products')
            ->get();

        return view('livewire.inventory.inventory-dashboard', [
            'totalProducts'    => $totalProducts,
            'lowStockCount'    => $lowStockCount,
            'expiringCount'    => $expiringCount,
            'totalValue'       => $totalValue,
            'lowStockItems'    => $lowStockItems,
            'expiringItems'    => $expiringItems,
            'recentMovements'  => $recentMovements,
            'categories'       => $categories,
        ])->layout('layouts.app');
    }
}
