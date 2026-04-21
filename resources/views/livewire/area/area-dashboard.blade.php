<div>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $area->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $area->name }}</h2>
            </div>
            <div class="flex flex-wrap gap-1">
                <button wire:click="setView('kanban')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'kanban' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📋 Kanban
                </button>
                <button wire:click="setView('monthly')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'monthly' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📅 Mensual
                </button>
                <button wire:click="setView('weekly')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'weekly' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    📆 Semanal
                </button>
                <button wire:click="setView('daily')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition"
                    style="{{ $view === 'daily' ? 'background:#4f46e5;color:white;' : 'background:#f3f4f6;color:#4b5563;' }}">
                    🕐 Diario
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- ═══ ESTADÍSTICAS ═══ --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left: 4px solid {{ $area->color }}">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left: 4px solid #3b82f6">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Inicio</p>
                    <p class="text-2xl font-bold mt-1" style="color:#2563eb">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left: 4px solid #f59e0b">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">En Proceso</p>
                    <p class="text-2xl font-bold mt-1" style="color:#d97706">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition" style="border-left: 4px solid #10b981">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Finalizado</p>
                    <p class="text-2xl font-bold mt-1" style="color:#059669">{{ $stats['completed'] }}</p>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- VISTA KANBAN + HISTORIAL (4 columnas)       --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- COLUMNA 1: INICIO --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#eff6ff">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#1e40af">
                            🔵 Inicio
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#bfdbfe;color:#1e40af">{{ $kanbanItems['pending']->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[70vh] overflow-y-auto">
                        @forelse($kanbanItems['pending'] as $woa)
                            @include('livewire.area._kanban-card', ['woa' => $woa, 'status' => 'pending'])
                        @empty
                            <div class="text-center py-12 text-gray-300">
                                <p class="text-3xl mb-2">📭</p>
                                <p class="text-sm">Sin trabajos</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- COLUMNA 2: EN PROCESO --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#fffbeb">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#92400e">
                            🟡 En Proceso
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#fde68a;color:#92400e">{{ $kanbanItems['in_progress']->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[70vh] overflow-y-auto">
                        @forelse($kanbanItems['in_progress'] as $woa)
                            @include('livewire.area._kanban-card', ['woa' => $woa, 'status' => 'in_progress'])
                        @empty
                            <div class="text-center py-12 text-gray-300">
                                <p class="text-3xl mb-2">📭</p>
                                <p class="text-sm">Sin trabajos</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- COLUMNA 3: FINALIZADO --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#ecfdf5">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#065f46">
                            🟢 Finalizado
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#a7f3d0;color:#065f46">{{ $kanbanItems['completed']->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[70vh] overflow-y-auto">
                        @forelse($kanbanItems['completed'] as $woa)
                            @include('livewire.area._kanban-card', ['woa' => $woa, 'status' => 'completed'])
                        @empty
                            <div class="text-center py-12 text-gray-300">
                                <p class="text-3xl mb-2">📭</p>
                                <p class="text-sm">Sin trabajos</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- COLUMNA 4: HISTORIAL DE TRABAJOS --}}
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b" style="background:#f5f3ff">
                        <h3 class="font-bold text-sm flex items-center gap-2" style="color:#5b21b6">
                            📜 Historial
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#ddd6fe;color:#5b21b6">{{ $historyItems->count() }}</span>
                        </h3>
                    </div>
                    <div class="p-3 space-y-2 min-h-[200px] max-h-[70vh] overflow-y-auto">
                        @forelse($historyItems as $woa)
                        <div class="rounded-lg border p-3" style="background:#faf5ff;border-color:#e9d5ff">
                            <div class="flex justify-between items-start mb-1">
                                <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="text-xs font-mono font-bold" style="color:#4f46e5" wire:navigate>
                                    {{ $woa->workOrder->code }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:#d1fae5;color:#065f46">
                                    ✓ Completado
                                </span>
                            </div>
                            <p class="text-sm font-medium text-gray-800">{{ $woa->workOrder->patient_name }}</p>
                            <p class="text-xs text-gray-500">Dr. {{ $woa->workOrder->doctor_name }}</p>
                            @if($woa->assignedUser)
                                <p class="text-xs text-gray-400 mt-1">👤 {{ $woa->assignedUser->name }}</p>
                            @endif
                            @if($woa->started_at && $woa->completed_at)
                            <div class="mt-1 flex items-center gap-2 text-xs text-gray-400">
                                <span>⏱️
                                @php $diff = $woa->started_at->diff($woa->completed_at); @endphp
                                @if($diff->days > 0) {{ $diff->days }}d {{ $diff->h }}h
                                @else {{ $diff->h }}h {{ $diff->i }}m
                                @endif
                                </span>
                                <span>· {{ $woa->completed_at->format('d/m/Y') }}</span>
                            </div>
                            @endif
                        </div>
                        @empty
                            <div class="text-center py-12 text-gray-300">
                                <p class="text-3xl mb-2">📋</p>
                                <p class="text-sm">Sin historial aún</p>
                                <p class="text-xs mt-1 text-gray-300">Los trabajos finalizados aparecerán aquí</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- VISTA CALENDARIO MENSUAL                    --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'monthly')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f9fafb">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">
                        ← Anterior
                    </button>
                    <h3 class="text-lg font-bold text-gray-800 capitalize">
                        {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat('F Y') }}
                    </h3>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 rounded-lg hover:opacity-80 transition text-sm font-medium text-white" style="background:#4f46e5">Hoy</button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">
                            Siguiente →
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 border-b" style="background:#f9fafb">
                    @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $day)
                        <div class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $day }}</div>
                    @endforeach
                </div>
                @foreach($monthGrid as $week)
                <div class="grid grid-cols-7 border-b last:border-b-0">
                    @foreach($week as $day)
                    <div wire:click="selectDay('{{ $day['date']->format('Y-m-d') }}')"
                         class="min-h-[100px] p-1.5 border-r last:border-r-0 cursor-pointer transition-colors"
                         style="{{ !$day['inMonth'] ? 'background:#f9fafb;opacity:0.5;' : '' }}{{ $day['isToday'] ? 'background:#eff6ff;box-shadow:inset 0 0 0 2px #93c5fd;' : '' }}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium {{ $day['isToday'] ? 'font-bold' : '' }}" style="color:{{ $day['isToday'] ? '#1d4ed8' : ($day['inMonth'] ? '#374151' : '#9ca3af') }}">
                                {{ $day['date']->day }}
                            </span>
                            @if(count($day['items']))
                                <span class="w-5 h-5 flex items-center justify-center rounded-full text-xs font-bold text-white" style="background-color: {{ $area->color }}">
                                    {{ count($day['items']) }}
                                </span>
                            @endif
                        </div>
                        <div class="space-y-0.5">
                            @foreach(array_slice($day['items'], 0, 3) as $woa)
                                <div class="text-xs px-1.5 py-0.5 rounded truncate"
                                     style="{{ $woa->kanban_status->value === 'completed' ? 'background:#d1fae5;color:#065f46' : ($woa->kanban_status->value === 'in_progress' ? 'background:#fef3c7;color:#92400e' : 'background:#dbeafe;color:#1e40af') }}">
                                    {{ $woa->workOrder->code }}
                                </div>
                            @endforeach
                            @if(count($day['items']) > 3)
                                <p class="text-xs text-gray-400 text-center">+{{ count($day['items']) - 3 }} más</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- VISTA CALENDARIO SEMANAL                    --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'weekly')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f9fafb">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">← Anterior</button>
                    <div class="text-center">
                        @php
                            $wStart = \Carbon\Carbon::parse($selectedDate)->startOfWeek(\Carbon\Carbon::MONDAY);
                            $wEnd = \Carbon\Carbon::parse($selectedDate)->endOfWeek(\Carbon\Carbon::SUNDAY);
                        @endphp
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $wStart->locale('es')->translatedFormat('d M') }} — {{ $wEnd->locale('es')->translatedFormat('d M Y') }}
                        </h3>
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
                            <div wire:click="selectDay('{{ $day['date']->format('Y-m-d') }}')"
                                 class="p-2 rounded-lg border text-xs cursor-pointer hover:shadow transition-all"
                                 style="{{ $woa->kanban_status->value === 'completed' ? 'background:#ecfdf5;border-color:#a7f3d0' : ($woa->kanban_status->value === 'in_progress' ? 'background:#fffbeb;border-color:#fde68a' : 'background:#eff6ff;border-color:#bfdbfe') }}">
                                <p class="font-mono font-bold" style="color:#4f46e5">{{ $woa->workOrder->code }}</p>
                                <p class="text-gray-700 truncate">{{ $woa->workOrder->patient_name }}</p>
                                @if($woa->assignedUser)
                                    <p class="text-gray-400 mt-0.5">👤 {{ Str::before($woa->assignedUser->name, ' ') }}</p>
                                @endif
                            </div>
                            @endforeach
                            @if(empty($day['items']))
                                <p class="text-center text-gray-300 text-xs py-6">—</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════ --}}
            {{-- VISTA CALENDARIO DIARIO                     --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($view === 'daily')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="background:#f9fafb">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border rounded-lg hover:bg-gray-50 transition text-sm font-medium text-gray-700">← Anterior</button>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-800 capitalize">
                            {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat('l, d \d\e F Y') }}
                        </h3>
                        <span class="text-sm text-gray-500">Vista Diaria — {{ $area->name }}</span>
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
                                <div class="w-2 h-12 rounded-full" style="background-color: {{ $area->color }}"></div>
                                <div>
                                    <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono font-bold text-sm" style="color:#4f46e5" wire:navigate>{{ $woa->workOrder->code }}</a>
                                    <p class="text-sm text-gray-800 font-medium">{{ $woa->workOrder->patient_name }}</p>
                                    <p class="text-xs text-gray-500">Dr. {{ $woa->workOrder->doctor_name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                @if($woa->assignedUser)
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">Responsable</p>
                                        <p class="text-sm font-medium text-gray-700">{{ $woa->assignedUser->name }}</p>
                                    </div>
                                @endif
                                <span class="px-3 py-1 rounded-full text-xs font-bold"
                                    style="{{ $woa->kanban_status->value === 'completed' ? 'background:#d1fae5;color:#065f46' : ($woa->kanban_status->value === 'in_progress' ? 'background:#fef3c7;color:#92400e' : 'background:#dbeafe;color:#1e40af') }}">
                                    {{ $woa->kanban_status->label() }}
                                </span>
                            </div>
                        </div>
                        @if($woa->stages->count())
                        <div class="p-4">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Checklist de Etapas</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($woa->stages as $stage)
                                <label wire:click="toggleStage({{ $stage->id }})"
                                       class="flex items-center gap-3 p-2 rounded-lg cursor-pointer transition"
                                       style="{{ $stage->is_completed ? 'background:#ecfdf5;border:1px solid #a7f3d0' : 'background:#f9fafb;border:1px solid #e5e7eb' }}">
                                    <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0"
                                         style="{{ $stage->is_completed ? 'background:#10b981;border:2px solid #10b981;color:white' : 'border:2px solid #d1d5db' }}">
                                        @if($stage->is_completed)
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium" style="{{ $stage->is_completed ? 'color:#065f46;text-decoration:line-through' : 'color:#374151' }}">
                                            {{ $stage->areaStage->name }}
                                        </p>
                                        @if($stage->performer)
                                            <p class="text-xs text-gray-400">✓ {{ $stage->performer->name }}</p>
                                        @endif
                                    </div>
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
                            <p class="text-sm mt-1">Selecciona otro día desde el calendario mensual.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
