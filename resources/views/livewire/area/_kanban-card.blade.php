{{-- Kanban Card Partial --}}
<div class="rounded-lg border p-3 hover:shadow-md transition-all" style="background:#f9fafb;border-color:#e5e7eb">
    {{-- Header --}}
    <div class="flex justify-between items-start mb-2">
        <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="text-sm font-mono font-bold" style="color:#4f46e5" wire:navigate>
            {{ $woa->workOrder->code }}
        </a>
        @if($woa->workOrder->priority)
        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
              style="{{ $woa->workOrder->priority->value === 'urgent' ? 'background:#fee2e2;color:#991b1b' : ($woa->workOrder->priority->value === 'high' ? 'background:#ffedd5;color:#9a3412' : 'background:#f3f4f6;color:#6b7280') }}">
            {{ $woa->workOrder->priority->label() }}
        </span>
        @endif
    </div>
    {{-- Paciente --}}
    <p class="text-sm font-medium text-gray-800">{{ $woa->workOrder->patient_name }}</p>
    <p class="text-xs text-gray-500">Dr. {{ $woa->workOrder->doctor_name }}</p>
    {{-- Progreso --}}
    <div class="mt-2">
        <div class="flex justify-between text-xs text-gray-400 mb-1">
            <span>Progreso</span>
            <span class="font-medium">{{ $woa->progress }}%</span>
        </div>
        <div class="w-full rounded-full h-1.5" style="background:#e5e7eb">
            <div class="h-1.5 rounded-full transition-all" style="width:{{ $woa->progress }}%;background:{{ $status === 'completed' ? '#10b981' : ($status === 'in_progress' ? '#f59e0b' : '#3b82f6') }}"></div>
        </div>
    </div>
    {{-- Responsable --}}
    <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
        @if($woa->assignedUser)
            <span>👤 {{ $woa->assignedUser->name }}</span>
        @else
            <span class="text-gray-300 italic">Sin asignar</span>
        @endif
        @if($woa->supervisor)
            <span>🔬 {{ $woa->supervisor->name }}</span>
        @endif
    </div>
    {{-- Botones Kanban --}}
    <div class="mt-3 pt-2 flex justify-between" style="border-top:1px solid #e5e7eb">
        @if($status !== 'pending')
            <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'pending')" class="text-xs font-medium transition" style="color:#2563eb">← Inicio</button>
        @else <span></span> @endif
        @if($status !== 'in_progress')
            <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'in_progress')" class="text-xs font-medium transition" style="color:#d97706">En Proceso</button>
        @endif
        @if($status !== 'completed')
            <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'completed')" class="text-xs font-medium transition" style="color:#059669">Finalizar →</button>
        @else <span></span> @endif
    </div>
</div>
