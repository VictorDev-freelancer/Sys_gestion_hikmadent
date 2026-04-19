<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\Reports;
use App\Livewire\WorkOrder\WorkOrderList;
use App\Livewire\WorkOrder\WorkOrderForm;
use App\Livewire\WorkOrder\WorkOrderDetail;
use App\Livewire\Area\AreaDashboard;

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

        // Módulo Exclusivo para Gestión de Usuarios
        Route::get('/usuarios', UserManagement::class)
            ->middleware('role:Super usuario') // Este requiere super admin puro
            ->name('admin.users');

        // Órdenes de Trabajo (Listado General)
        Route::get('/ordenes', WorkOrderList::class)
            ->name('work-orders.index');

        Route::get('/ordenes/crear', WorkOrderForm::class)
            ->name('work-orders.create');

        Route::get('/ordenes/{workOrder}', WorkOrderDetail::class)
            ->name('work-orders.show');
    });

    // --------------------------------------------------------
    // DASHBOARD POR ÁREA (acceso por slug)
    // --------------------------------------------------------
    Route::get('/area/{slug}', AreaDashboard::class)
        ->name('area.dashboard');
});
