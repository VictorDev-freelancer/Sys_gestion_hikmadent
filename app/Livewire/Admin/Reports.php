<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Livewire Reports
 *
 * [SRP] Interfaz de usuario para descargar reportes (ETL).
 */
#[Layout('layouts.app')]
class Reports extends Component
{
    public $startDate;
    public $endDate;
    public $areaId = '';

    public function mount()
    {
        // Por defecto el mes actual
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
    }

    public function downloadReport(ReportExportService $exportService)
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
            'areaId'    => 'nullable|exists:areas,id',
        ]);

        return $exportService->exportWorkOrdersToCsv(
            $this->startDate,
            $this->endDate,
            $this->areaId ?: null
        );
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'areas' => Area::active()->ordered()->get(),
        ]);
    }
}
