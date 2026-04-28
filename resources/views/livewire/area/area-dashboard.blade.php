<div>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $area->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $area->name }}</h2>
            </div>
            <div class="flex flex-wrap gap-1">
                <button wire:click="setView('kanban')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'kanban' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📋 Kanban
                </button>
                <button wire:click="setView('monthly')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'monthly' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📅 Mensual
                </button>
                <button wire:click="setView('weekly')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'weekly' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📆 Semanal
                </button>
                <button wire:click="setView('daily')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'daily' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    🕐 Diario
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

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

            {{-- ═══════════════════════════════════════════ --}}
            {{-- VISTA KANBAN                                --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'kanban')

            {{-- FILA 1: Kanban — 3 columnas --}}
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
                            {{-- Progreso al 100% --}}
                            <div class="mt-2">
                                <div class="w-full rounded-full h-1.5" style="background:#e5e7eb">
                                    <div class="h-1.5 rounded-full" style="width:{{ $woa->progress }}%;background:#10b981"></div>
                                </div>
                                <p class="text-xs text-right font-medium mt-1" style="color:#059669">{{ $woa->progress }}%</p>
                            </div>
                            {{-- Botón CONFIRMAR ENTREGA --}}
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

            {{-- FILA 2: HISTORIAL DE TRABAJOS — ancho completo --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f5f3ff">
                    <h3 class="font-bold text-base flex items-center gap-2" style="color:#5b21b6">
                        📜 Historial de Trabajos Realizados
                    </h3>
                    <span class="text-xs font-bold px-3 py-1 rounded-full" style="background:#ddd6fe;color:#5b21b6">{{ $historyItems->count() }} entregas</span>
                </div>

                @if($historyItems->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y" style="border-color:#e5e7eb">
                        <thead>
                            <tr style="background:#faf5ff">
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Código OT</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Paciente</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Doctor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Responsable</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Fecha Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Fecha Entrega</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Duración</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y" style="border-color:#f3f4f6">
                            @foreach($historyItems as $woa)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono text-sm font-bold" style="color:#4f46e5" wire:navigate>{{ $woa->workOrder->code }}</a>
                                </td>
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
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold" style="background:#d1fae5;color:#065f46">✅ Entregado</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12 text-gray-300">
                    <p class="text-4xl mb-3">📋</p>
                    <p class="text-lg font-medium">Sin entregas registradas</p>
                    <p class="text-sm mt-1">Cuando confirmes entregas en la columna "Finalizado", aparecerán aquí.</p>
                </div>
                @endif
            </div>

            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- CALENDARIO MENSUAL                          --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'monthly')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f9fafb">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">← Anterior</button>
                    <h3 class="text-lg font-bold text-gray-800 capitalize">{{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat('F Y') }}</h3>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 rounded-lg text-sm font-medium text-white" style="background:#4f46e5">Hoy</button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">Siguiente →</button>
                    </div>
                </div>
                <div class="grid grid-cols-7 border-b" style="background:#f9fafb">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                        <div class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">{{ $d }}</div>
                    @endforeach
                </div>
                @foreach($monthGrid as $week)
                <div class="grid grid-cols-7 border-b last:border-b-0">
                    @foreach($week as $day)
                    <div wire:click="selectDay('{{ $day['date']->format('Y-m-d') }}')"
                         class="min-h-[100px] p-1.5 border-r last:border-r-0 cursor-pointer transition-colors"
                         style="{{ !$day['inMonth'] ? 'background:#f9fafb;opacity:0.5;' : '' }}{{ $day['isToday'] ? 'background:#eff6ff;box-shadow:inset 0 0 0 2px #93c5fd;' : '' }}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm {{ $day['isToday'] ? 'font-bold' : 'font-medium' }}" style="color:{{ $day['isToday'] ? '#1d4ed8' : ($day['inMonth'] ? '#374151' : '#9ca3af') }}">{{ $day['date']->day }}</span>
                            @if(count($day['items']))
                                <span class="w-5 h-5 flex items-center justify-center rounded-full text-xs font-bold text-white" style="background:{{ $area->color }}">{{ count($day['items']) }}</span>
                            @endif
                        </div>
                        @foreach(array_slice($day['items'], 0, 3) as $woa)
                            <div class="text-xs px-1.5 py-0.5 rounded truncate mb-0.5"
                                 style="{{ $woa->kanban_status->value === 'completed' ? 'background:#d1fae5;color:#065f46' : ($woa->kanban_status->value === 'in_progress' ? 'background:#fef3c7;color:#92400e' : 'background:#dbeafe;color:#1e40af') }}">
                                {{ $woa->workOrder->code }}
                            </div>
                        @endforeach
                        @if(count($day['items']) > 3) <p class="text-xs text-gray-400 text-center">+{{ count($day['items']) - 3 }}</p> @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- CALENDARIO SEMANAL                          --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'weekly')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f9fafb">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">← Anterior</button>
                    <div class="text-center">
                        @php $ws = \Carbon\Carbon::parse($selectedDate)->startOfWeek(\Carbon\Carbon::MONDAY); $we = \Carbon\Carbon::parse($selectedDate)->endOfWeek(\Carbon\Carbon::SUNDAY); @endphp
                        <h3 class="text-lg font-bold text-gray-800">{{ $ws->locale('es')->translatedFormat('d M') }} — {{ $we->locale('es')->translatedFormat('d M Y') }}</h3>
                        <span class="text-sm text-gray-500">Vista Semanal</span>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 rounded-lg text-sm font-medium text-white" style="background:#4f46e5">Hoy</button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">Siguiente →</button>
                    </div>
                </div>
                <div class="grid grid-cols-7 divide-x">
                    @foreach($weekGrid as $day)
                    <div class="min-h-[400px]" style="{{ $day['isToday'] ? 'background:#eff6ff' : '' }}">
                        <div class="px-2 py-2 border-b text-center" style="{{ $day['isToday'] ? 'background:#dbeafe' : 'background:#f9fafb' }}">
                            <p class="text-xs font-bold text-gray-500 uppercase">{{ $day['date']->locale('es')->translatedFormat('D') }}</p>
                            <p class="text-lg font-bold" style="color:{{ $day['isToday'] ? '#1d4ed8' : '#1f2937' }}">{{ $day['date']->day }}</p>
                        </div>
                        <div class="p-1.5 space-y-1.5">
                            @foreach($day['items'] as $woa)
                            <div wire:click="selectDay('{{ $day['date']->format('Y-m-d') }}')" class="p-2 rounded-lg border text-xs cursor-pointer hover:shadow transition"
                                 style="{{ $woa->kanban_status->value === 'completed' ? 'background:#ecfdf5;border-color:#a7f3d0' : ($woa->kanban_status->value === 'in_progress' ? 'background:#fffbeb;border-color:#fde68a' : 'background:#eff6ff;border-color:#bfdbfe') }}">
                                <p class="font-mono font-bold" style="color:#4f46e5">{{ $woa->workOrder->code }}</p>
                                <p class="text-gray-700 truncate">{{ $woa->workOrder->patient_name }}</p>
                            </div>
                            @endforeach
                            @if(empty($day['items'])) <p class="text-center text-gray-300 text-xs py-6">—</p> @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- CALENDARIO DIARIO                           --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'daily')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f9fafb">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">← Anterior</button>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-800 capitalize">{{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat('l, d \d\e F Y') }}</h3>
                        <span class="text-sm text-gray-500">{{ $area->name }}</span>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 rounded-lg text-sm font-medium text-white" style="background:#4f46e5">Hoy</button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">Siguiente →</button>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($daySchedule as $woa)
                    <div class="border rounded-xl overflow-hidden mb-4 hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between p-4 border-b" style="background:#f9fafb">
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-12 rounded-full" style="background:{{ $area->color }}"></div>
                                <div>
                                    <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono font-bold text-sm" style="color:#4f46e5" wire:navigate>{{ $woa->workOrder->code }}</a>
                                    <p class="text-sm text-gray-800 font-medium">{{ $woa->workOrder->patient_name }}</p>
                                    <p class="text-xs text-gray-500">Dr. {{ $woa->workOrder->doctor_name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                @if($woa->assignedUser)
                                <div class="text-right"><p class="text-xs text-gray-400">Responsable</p><p class="text-sm font-medium text-gray-700">{{ $woa->assignedUser->name }}</p></div>
                                @endif
                                <span class="px-3 py-1 rounded-full text-xs font-bold"
                                    style="{{ $woa->kanban_status->value === 'completed' ? 'background:#d1fae5;color:#065f46' : ($woa->kanban_status->value === 'in_progress' ? 'background:#fef3c7;color:#92400e' : 'background:#dbeafe;color:#1e40af') }}">
                                    {{ $woa->kanban_status->label() }}
                                </span>
                            </div>
                        </div>
                        @if($woa->stages->count())
                        <div class="p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Checklist</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($woa->stages as $stage)
                                <label wire:click="toggleStage({{ $stage->id }})" class="flex items-center gap-3 p-2 rounded-lg cursor-pointer transition"
                                       style="{{ $stage->is_completed ? 'background:#ecfdf5;border:1px solid #a7f3d0' : 'background:#f9fafb;border:1px solid #e5e7eb' }}">
                                    <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0"
                                         style="{{ $stage->is_completed ? 'background:#10b981;border:2px solid #10b981;color:white' : 'border:2px solid #d1d5db' }}">
                                        @if($stage->is_completed)<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>@endif
                                    </div>
                                    <p class="text-sm font-medium" style="{{ $stage->is_completed ? 'color:#065f46;text-decoration:line-through' : 'color:#374151' }}">{{ $stage->areaStage->name }}</p>
                                </label>
                                @endforeach
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all" style="width:{{ $woa->progress }}%;background:#10b981"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-600">{{ $woa->progress }}%</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-16 text-gray-300">
                        <p class="text-5xl mb-3">📭</p>
                        <p class="text-lg font-medium">Sin trabajos para esta fecha</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
