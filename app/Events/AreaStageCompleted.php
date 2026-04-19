<?php

namespace App\Events;

use App\Models\WorkOrderAreaStage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event AreaStageCompleted
 *
 * Se emite cuando un técnico completa una etapa del checklist.
 * Permite al supervisor y administración ver el progreso en tiempo real.
 */
class AreaStageCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkOrderAreaStage $stage,
        public int $performedBy,
    ) {}

    public function broadcastOn(): array
    {
        $workOrderArea = $this->stage->workOrderArea;

        return [
            new Channel('area.' . $workOrderArea->area_id),
            new Channel('work-order.' . $workOrderArea->work_order_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stage.completed';
    }

    public function broadcastWith(): array
    {
        $workOrderArea = $this->stage->workOrderArea;

        return [
            'work_order_id' => $workOrderArea->work_order_id,
            'area_id'       => $workOrderArea->area_id,
            'stage_name'    => $this->stage->areaStage->name,
            'progress'      => $workOrderArea->progress,
        ];
    }
}
