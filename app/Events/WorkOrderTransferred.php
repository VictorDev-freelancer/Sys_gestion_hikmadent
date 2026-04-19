<?php

namespace App\Events;

use App\Models\WorkOrder;
use App\Models\Area;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event WorkOrderTransferred
 *
 * Se emite cuando una Orden de Trabajo se transfiere entre áreas.
 * Permite notificar al responsable del área destino en tiempo real.
 */
class WorkOrderTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkOrder $workOrder,
        public ?Area $fromArea,
        public Area $toArea,
        public int $performedBy,
        public ?string $notes = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('area.' . $this->toArea->id),
            new Channel('work-orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'work-order.transferred';
    }

    public function broadcastWith(): array
    {
        return [
            'work_order_id'  => $this->workOrder->id,
            'code'           => $this->workOrder->code,
            'patient_name'   => $this->workOrder->patient_name,
            'from_area'      => $this->fromArea?->name ?? 'Administración',
            'to_area'        => $this->toArea->name,
            'doctor_name'    => $this->workOrder->doctor_name,
        ];
    }
}
