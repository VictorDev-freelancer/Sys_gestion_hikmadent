<?php

namespace App\Providers;

use App\Models\WorkOrder;
use App\Models\WorkOrderArea;
use App\Observers\WorkOrderObserver;
use App\Observers\WorkOrderAreaObserver;
use App\Services\WorkOrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * [SOLID - DIP] Registrar el servicio como singleton
     * para inyección de dependencias.
     */
    public function register(): void
    {
        $this->app->singleton(WorkOrderService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * [SOLID - SRP] Los observers se registran aquí, no en los modelos,
     * manteniendo los modelos limpios y enfocados en datos.
     */
    public function boot(): void
    {
        WorkOrder::observe(WorkOrderObserver::class);
        WorkOrderArea::observe(WorkOrderAreaObserver::class);
    }
}
