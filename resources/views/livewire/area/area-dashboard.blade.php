<div>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $area->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $area->name }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- Botones de vista --}}
            <div class="flex justify-end mb-4 gap-1">
                <button wire:click="setView('kanban')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'kanban' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📋 Kanban
                </button>
                <button wire:click="setView('calendar')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'calendar' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📅 Calendario
                </button>
            </div>

            {{-- ═══ ESTADÍSTICAS ═══ --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left:4px solid {{ $area->color }}">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left:4px solid #3b82f6">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Asignado</p>
                    <p class="text-2xl font-bold mt-1" style="color:#2563eb">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left:4px solid #f59e0b">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">En desarrollo</p>
                    <p class="text-2xl font-bold mt-1" style="color:#d97706">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left:4px solid #10b981">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Completado</p>
                    <p class="text-2xl font-bold mt-1" style="color:#059669">{{ $stats['completed'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left:4px solid #8b5cf6">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Entregados</p>
                    <p class="text-2xl font-bold mt-1" style="color:#7c3aed">{{ $stats['delivered'] }}</p>
                </div>
            </div>

            {{-- ═══ VISTA KANBAN ═══ --}}
            @if($view === 'kanban')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                {{-- ASIGNADO --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#eff6ff">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#1e40af">
                            🔵 Asignado
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#bfdbfe;color:#1e40af">{{ $kanbanItems['pending']->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[65vh] overflow-y-auto">
                        @forelse($kanbanItems['pending'] as $woa)
                            @include('livewire.area._kanban-card', ['woa' => $woa, 'status' => 'pending'])
                        @empty
                            <div class="text-center py-12 text-gray-300"><p class="text-3xl mb-2">📭</p><p class="text-sm">Sin trabajos</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- EN DESARROLLO --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#fffbeb">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#92400e">
                            🟡 En desarrollo
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#fde68a;color:#92400e">{{ $kanbanItems['in_progress']->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[65vh] overflow-y-auto">
                        @forelse($kanbanItems['in_progress'] as $woa)
                            @include('livewire.area._kanban-card', ['woa' => $woa, 'status' => 'in_progress'])
                        @empty
                            <div class="text-center py-12 text-gray-300"><p class="text-3xl mb-2">📭</p><p class="text-sm">Sin trabajos</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- COMPLETADO --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#ecfdf5">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#065f46">
                            🟢 Completado
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#a7f3d0;color:#065f46">{{ $kanbanItems['completed']->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[65vh] overflow-y-auto">
                        @forelse($kanbanItems['completed'] as $woa)
                        <div class="rounded-lg border p-3 transition-all" style="background:#f9fafb;border-color:#e5e7eb">
                            <div class="flex justify-between items-start mb-2">
                                <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="text-sm font-mono font-bold" style="color:#4f46e5" wire:navigate>{{ $woa->workOrder->code }}</a>
                                @if($woa->workOrder->priority)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                      style="{{ $woa->workOrder->priority->value === 'urgent' ? 'background:#fee2e2;color:#991b1b' : 'background:#f3f4f6;color:#6b7280' }}">
                                    {{ $woa->workOrder->priority->label() }}
                                </span>
                                @endif
                            </div>
                            <p class="text-sm font-medium text-gray-800">{{ $woa->workOrder->patient_name }}</p>
                            <p class="text-xs text-gray-500">Dr. {{ $woa->workOrder->doctor_name }}</p>
                            <div class="mt-2">
                                <div class="w-full rounded-full h-1.5" style="background:#e5e7eb">
                                    <div class="h-1.5 rounded-full" style="width:{{ $woa->progress }}%;background:#10b981"></div>
                                </div>
                                <p class="text-xs text-right font-medium mt-1" style="color:#059669">{{ $woa->progress }}%</p>
                            </div>
                            <div class="mt-3 pt-2" style="border-top:1px solid #e5e7eb">
                                <button wire:click="confirmDelivery({{ $woa->id }})"
                                        wire:confirm="¿Confirmar entrega de {{ $woa->workOrder->code }}? Esta acción moverá la orden al Historial."
                                        class="w-full py-2 rounded-lg text-sm font-bold text-white transition hover:opacity-90"
                                        style="background:linear-gradient(135deg,#059669,#10b981)">
                                    ✅ Confirmar Entrega
                                </button>
                            </div>
                        </div>
                        @empty
                            <div class="text-center py-12 text-gray-300"><p class="text-3xl mb-2">📭</p><p class="text-sm">Sin trabajos</p></div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- HISTORIAL --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f5f3ff">
                    <h3 class="font-bold text-base flex items-center gap-2" style="color:#5b21b6">📜 Historial de Trabajos Realizados</h3>
                    <span class="text-xs font-bold px-3 py-1 rounded-full" style="background:#ddd6fe;color:#5b21b6">{{ $historyItems->count() }} entregas</span>
                </div>
                @if($historyItems->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y" style="border-color:#e5e7eb">
                        <thead>
                            <tr style="background:#faf5ff">
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Código OT</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Paciente</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Doctor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Responsable</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Fecha Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Fecha Entrega</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Duración</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:#6b7280">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y" style="border-color:#f3f4f6">
                            @foreach($historyItems as $woa)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3"><a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono text-sm font-bold" style="color:#4f46e5" wire:navigate>{{ $woa->workOrder->code }}</a></td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $woa->workOrder->patient_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">Dr. {{ $woa->workOrder->doctor_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $woa->assignedUser?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $woa->started_at ? $woa->started_at->format('d/m/Y H:i') : '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $woa->completed_at ? $woa->completed_at->format('d/m/Y H:i') : '—' }}</td>
                                <td class="px-4 py-3 text-xs font-medium text-gray-700">
                                    @if($woa->started_at && $woa->completed_at)
                                        @php $diff = $woa->started_at->diff($woa->completed_at); @endphp
                                        {{ $diff->days > 0 ? $diff->days.'d '.$diff->h.'h' : $diff->h.'h '.$diff->i.'m' }}
                                    @else — @endif
                                </td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-bold" style="background:#d1fae5;color:#065f46">✅ Entregado</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12 text-gray-300">
                    <p class="text-4xl mb-3">📋</p>
                    <p class="text-lg font-medium">Sin entregas registradas</p>
                </div>
                @endif
            </div>

            @endif

            {{-- ═══ VISTA FULLCALENDAR ═══ --}}
            @if($view === 'calendar')
            <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 flex items-center justify-between" style="background:linear-gradient(135deg,{{ $area->color }}, {{ $area->color }}cc);border-bottom:2px solid {{ $area->color }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📅</span>
                        <div>
                            <h3 class="font-bold text-white text-lg">Calendario — {{ $area->name }}</h3>
                            <p class="text-white/70 text-xs">Órdenes de trabajo activas organizadas por fecha</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-white/80">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:#3b82f6"></span> Asignado
                        <span class="w-3 h-3 rounded-full inline-block ml-2" style="background:#f59e0b"></span> En desarrollo
                        <span class="w-3 h-3 rounded-full inline-block ml-2" style="background:#10b981"></span> Completado
                        <span class="w-3 h-3 rounded-full inline-block ml-2" style="background:#ef4444"></span> Retrasada
                    </div>
                </div>
                <div class="p-4" wire:ignore>
                    <div id="area-calendar" x-data="fullcalendar(@js($calendarEvents))"></div>
                </div>
            </div>

            <style>
                #area-calendar .fc { font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; }
                #area-calendar .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; color: #1f2937 !important; text-transform: capitalize; }
                #area-calendar .fc-button { background: {{ $area->color }} !important; border-color: {{ $area->color }} !important; font-weight: 600 !important; font-size: 0.8rem !important; padding: 6px 14px !important; border-radius: 8px !important; }
                #area-calendar .fc-button:hover { filter: brightness(0.85) !important; transform: translateY(-1px); }
                #area-calendar .fc-button-active { filter: brightness(0.75) !important; }
                #area-calendar .fc-today-button { background: #059669 !important; border-color: #047857 !important; }
                #area-calendar .fc-day-today { background: {{ $area->color }}10 !important; }
                #area-calendar .fc-daygrid-day-number { font-weight: 600; color: #374151; padding: 6px 10px !important; }
                #area-calendar .fc-day-today .fc-daygrid-day-number { background: {{ $area->color }}; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; }
                #area-calendar .fc-event { border-radius: 6px !important; padding: 2px 6px !important; font-size: 0.72rem !important; font-weight: 600 !important; cursor: pointer !important; }
                #area-calendar .fc-event:hover { transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important; }
                #area-calendar .fc-col-header-cell { background: #f9fafb; font-weight: 700 !important; text-transform: uppercase !important; font-size: 0.7rem !important; color: #6b7280 !important; }
                .fc-area-tooltip { position: fixed; z-index: 9999; background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.12); pointer-events: none; min-width: 220px; max-width: 280px; }
            </style>
            @endif

        </div>
    </div>
</div>


