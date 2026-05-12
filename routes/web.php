<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\Reports;
use App\Livewire\WorkOrder\WorkOrderList;
use App\Livewire\WorkOrder\WorkOrderForm;
use App\Livewire\WorkOrder\WorkOrderDetail;
use App\Livewire\Area\AreaDashboard;
use App\Livewire\Inventory\InventoryDashboard;
use App\Livewire\Inventory\ProductManagement;
use App\Livewire\Inventory\StockMovements;
use App\Livewire\Inventory\KardexReport;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // --------------------------------------------------------
    // Dashboard Principal (Admin) - Se encarga de aislar/redirigir Técnicos.
    Route::get('/dashboard', AdminDashboard::class)
        ->name('dashboard');

    // --------------------------------------------------------
    // CONSOLA GERENCIAL Y ADMINISTRATIVA (Restringida rígidamente)
    // --------------------------------------------------------
    Route::middleware(['role:Super usuario|Administración'])->group(function () {
        
        // Módulo de Reportes ETL
        Route::get('/reportes', Reports::class)
            ->name('admin.reports');

        // Control y Gestión de Personal (Accesible para Super usuario y Administración)
        Route::get('/usuarios', UserManagement::class)
            ->middleware('role:Super usuario|Administración')
            ->name('admin.users');

        // Órdenes de Trabajo (Listado General)
        Route::get('/ordenes', WorkOrderList::class)
            ->name('work-orders.index');

        Route::get('/ordenes/crear', WorkOrderForm::class)
            ->name('work-orders.create');

        // --------------------------------------------------------
        // MÓDULO DE INVENTARIO (Solo Admin y Super usuario)
        // --------------------------------------------------------
        Route::prefix('inventario')->group(function () {
            Route::get('/', InventoryDashboard::class)
                ->name('inventory.dashboard');
            Route::get('/productos', ProductManagement::class)
                ->name('inventory.products');
            Route::get('/movimientos', StockMovements::class)
                ->name('inventory.movements');
            Route::get('/kardex', KardexReport::class)
                ->name('inventory.kardex');
        });

        // --------------------------------------------------------
        // MÓDULO DE CATÁLOGO (Precios de Trabajos)
        // --------------------------------------------------------
        Route::prefix('catalogo')->group(function () {
            Route::get('/', \App\Livewire\Admin\Catalog\CatalogList::class)
                ->name('admin.catalog.index');
            Route::get('/crear', \App\Livewire\Admin\Catalog\CatalogForm::class)
                ->name('admin.catalog.create');
            Route::get('/{id}/editar', \App\Livewire\Admin\Catalog\CatalogForm::class)
                ->name('admin.catalog.edit');
        });
    });

    // --------------------------------------------------------
    // DETALLE DE ORDEN (Accesible para todos los roles para transferencias)
    // --------------------------------------------------------
    Route::get('/ordenes/{workOrder}', WorkOrderDetail::class)
        ->name('work-orders.show');
        
    Route::get('/ordenes/{workOrder}/imprimir', function (\App\Models\WorkOrder $workOrder) {
        $workOrder->load(['currentArea', 'assignedTpd', 'workOrderAreas.area', 'workOrderAreas.stages.areaStage']);
        return view('print.work-order', compact('workOrder'));
    })->name('work-orders.print');

    // --------------------------------------------------------
    // DASHBOARD POR ÁREA (acceso por slug)
    // --------------------------------------------------------
    Route::get('/area/{slug}', AreaDashboard::class)
        ->name('area.dashboard');
});
