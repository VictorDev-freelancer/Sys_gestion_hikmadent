<?php

namespace App\Services;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReportExportService
 *
 * [SRP] Servicio encargado exclusiva y puramente de extraer (E),
 * transformar (T) y cargar (L - en CSV) la información de las órdenes
 * para propósitos de análisis y analítica en la Administración.
 */
class ReportExportService
{
    /**
     * Extrae todas las órdenes en un rango de fechas y devuelve un StreamedResponse
     * en formato CSV para descarga directa, protegiendo la memoria (memory limit).
     */
    public function exportWorkOrdersToCsv(?string $startDate, ?string $endDate, ?int $areaId = null): StreamedResponse
    {
        $query = WorkOrder::with(['client', 'assignedTpd', 'currentArea', 'traceabilityLogs']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        if ($areaId) {
            $query->whereHas('workOrderAreas', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        $query->orderBy('created_at', 'desc');

        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=Reporte_Productividad_HIKMADENT_' . now()->format('Ymd_His') . '.csv',
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];

        return response()->stream(function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // BOM para correcta visualización en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados del CSV
            fputcsv($file, [
                'Codigo OT',
                'Paciente',
                'Doctor',
                'Consultorio',
                'Tipo Trabajo',
                'Estado Actual',
                'Area Actual',
                'Prioridad',
                'Fecha Ingreso',
                'Fecha Entrega (Esperada)',
                'Dias Transcurridos',
                'Retrasado',
                'Total Areas Recorridas',
                'TPD Responsable'
            ]);

            // Chunk processing para evitar colapso de RAM en miles de registros
            $query->chunk(200, function (Collection $orders) use ($file) {
                foreach ($orders as $order) {
                    
                    // Transformación / Mapeo de datos (Transformation)
                    $daysElapsed = null;
                    if ($order->order_date) {
                        $end = $order->status->value === 'completed' && $order->updated_at 
                                ? $order->updated_at 
                                : now();
                        $daysElapsed = $order->order_date->diffInDays($end);
                    }

                    $isDelayed = 'No';
                    if ($order->status->value !== 'completed' && $order->delivery_date && $order->delivery_date->isPast()) {
                        $isDelayed = 'Sí';
                    }

                    // Carga (Load) de registro en el flujo
                    fputcsv($file, [
                        $order->code,
                        $order->patient_name,
                        $order->doctor_name,
                        $order->clinic_name ?? 'N/A',
                        $order->prosthetic_type->label(),
                        $order->status->label(),
                        $order->currentArea ? $order->currentArea->name : 'No Asignada',
                        $order->priority->label(),
                        $order->order_date ? $order->order_date->format('Y-m-d') : 'N/A',
                        $order->delivery_date ? $order->delivery_date->format('Y-m-d') : 'N/A',
                        $daysElapsed !== null ? $daysElapsed : 'N/A',
                        $isDelayed,
                        $order->traceabilityLogs->pluck('to_area_id')->filter()->unique()->count(),
                        $order->assignedTpd ? $order->assignedTpd->name : 'N/A',
                    ]);
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}
