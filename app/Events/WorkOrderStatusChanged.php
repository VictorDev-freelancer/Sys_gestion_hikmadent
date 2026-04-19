<?php

namespace App\Events;

use App\Models\WorkOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event WorkOrderStatusChanged
 *
 * [SOLID - DIP] Se emite cuando una Orden cambia de estado.
 * Los listeners suscritos reaccionan independientemente (auditoría,
 * notificaciones, actualización de Kanban, etc.).
 *
 * [NOTIFICACIONES EN TIEMPO REAL] Implementa ShouldBroadcast
 * para notificar vía WebSocket.
 */
class WorkOrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkOrder $workOrder,
        public string $fromStatus,
        public string $toStatus,
        public int $performedBy,
        public ?string $notes = null,
    ) {}

    /**
     * Canal de broadcasting para notificaciones en tiempo real.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('work-orders'),
        ];

        // Notificar también al canal del área actual
        if ($this->workOrder->current_area_id) {
            $channels[] = new Channel('area.' . $this->workOrder->current_area_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'work-order.status-changed';
    }

    public function broadcastWith(): array
    {
        return [
            'work_order_id'   => $this->workOrder->id,
            'code'            => $this->workOrder->code,
            'from_status'     => $this->fromStatus,
            'to_status'       => $this->toStatus,
            'patient_name'    => $this->workOrder->patient_name,
            'current_area'    => $this->workOrder->currentArea?->name,
        ];
    }
}
