<div>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $area->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $area->name }}</h2>
            </div>
            {{-- ═══ Tabs de Navegación ═══ --}}
            <div class="flex flex-wrap gap-1">
                <button wire:click="setView('kanban')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'kanban' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    📋 Kanban
                </button>
                <button wire:click="setCalendarMode('monthly')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'calendar' && $calendarMode === 'monthly' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    📅 Mensual
                </button>
                <button wire:click="setCalendarMode('weekly')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'calendar' && $calendarMode === 'weekly' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    📆 Semanal
                </button>
                <button wire:click="setCalendarMode('daily')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'calendar' && $calendarMode === 'daily' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    🕐 Diario
                </button>
                <button wire:click="setView('history')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'history' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    📜 Historial
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- TARJETAS DE ESTADÍSTICAS                            --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 hover:shadow-md transition" style="border-color: {{ $area->color }}">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500 hover:shadow-md transition">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Inicio</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-amber-500 hover:shadow-md transition">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">En Proceso</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-emerald-500 hover:shadow-md transition">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Finalizado</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['completed'] }}</p>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- 1. VISTA KANBAN                                     --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            @if($view === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach([
                    'pending'     => ['Inicio',     'blue',    '🔵'],
                    'in_progress' => ['En Proceso', 'amber',   '🟡'],
                    'completed'   => ['Finalizado', 'emerald', '🟢'],
                ] as $status => [$label, $color, $emoji])
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    {{-- Header columna --}}
                    <div class="px-4 py-3 border-b bg-{{ $color }}-50 flex items-center justify-between">
                        <h3 class="font-bold text-sm text-{{ $color }}-800 flex items-center gap-2">
                            <span>{{ $emoji }}</span> {{ $label }}
                        </h3>
                        <span class="bg-{{ $color }}-200 text-{{ $color }}-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
                            {{ $kanbanItems[$status]->count() }}
                        </span>
                    </div>
                    {{-- Cards --}}
                    <div class="p-3 space-y-3 min-h-[200px] max-h-[70vh] overflow-y-auto">
                        @foreach($kanbanItems[$status] as $woa)
                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 hover:shadow-md hover:border-{{ $color }}-300 transition-all duration-200">
                            {{-- Header card --}}
                            <div class="flex justify-between items-start mb-2">
                                <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="text-sm font-mono font-bold text-indigo-600 hover:text-indigo-800" wire:navigate>
                                    {{ $woa->workOrder->code }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $woa->workOrder->priority->color() }}-100 text-{{ $woa->workOrder->priority->color() }}-800">
                                    {{ $woa->workOrder->priority->label() }}
                                </span>
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
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-{{ $color }}-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $woa->progress }}%"></div>
                                </div>
                            </div>
                            {{-- Responsables --}}
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
                            <div class="mt-3 pt-2 border-t border-gray-200 flex justify-between">
                                @if($status !== 'pending')
                                    <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'pending')" class="text-xs font-medium text-blue-600 hover:text-blue-800 transition">← Inicio</button>
                                @else <span></span> @endif
                                @if($status !== 'in_progress')
                                    <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'in_progress')" class="text-xs font-medium text-amber-600 hover:text-amber-800 transition">En Proceso</button>
                                @endif
                                @if($status !== 'completed')
                                    <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'completed')" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 transition">Finalizar →</button>
                                @else <span></span> @endif
                            </div>
                        </div>
                        @endforeach

                        @if($kanbanItems[$status]->isEmpty())
                            <div class="text-center py-12 text-gray-300">
                                <p class="text-3xl mb-2">📭</p>
                                <p class="text-sm">Sin trabajos</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- 2. VISTA CALENDARIO MENSUAL                         --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            @if($view === 'calendar' && $calendarMode === 'monthly')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- Nav del calendario --}}
                <div class="bg-gray-50 px-6 py-4 border-b flex items-center justify-between">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                        ← Anterior
                    </button>
                    <h3 class="text-lg font-bold text-gray-800 capitalize">
                        {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat('F Y') }}
                    </h3>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition text-sm font-medium">
                            Hoy
                        </button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                            Siguiente →
                        </button>
                    </div>
                </div>
                {{-- Header días de la semana --}}
                <div class="grid grid-cols-7 border-b bg-gray-50">
                    @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $day)
                        <div class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $day }}</div>
                    @endforeach
                </div>
                {{-- Grid de días --}}
                @foreach($monthGrid as $week)
                <div class="grid grid-cols-7 border-b last:border-b-0">
                    @foreach($week as $day)
                    <div wire:click="selectDay('{{ $day['date']->format('Y-m-d') }}')"
                         class="min-h-[100px] p-1.5 border-r last:border-r-0 cursor-pointer hover:bg-indigo-50 transition-colors
                                {{ !$day['inMonth'] ? 'bg-gray-50 opacity-50' : '' }}
                                {{ $day['isToday'] ? 'bg-blue-50 ring-2 ring-inset ring-blue-300' : '' }}">
                        {{-- Número del día --}}
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium {{ $day['isToday'] ? 'text-blue-700 font-bold' : ($day['inMonth'] ? 'text-gray-700' : 'text-gray-400') }}">
                                {{ $day['date']->day }}
                            </span>
                            @if(count($day['items']))
                                <span class="w-5 h-5 flex items-center justify-center rounded-full text-xs font-bold text-white" style="background-color: {{ $area->color }}">
                                    {{ count($day['items']) }}
                                </span>
                            @endif
                        </div>
                        {{-- Órdenes en la celda (max 3 visibles) --}}
                        <div class="space-y-0.5">
                            @foreach(array_slice($day['items'], 0, 3) as $woa)
                                <div class="text-xs px-1.5 py-0.5 rounded truncate
                                    {{ $woa->kanban_status->value === 'completed' ? 'bg-emerald-100 text-emerald-700' :
                                       ($woa->kanban_status->value === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
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

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- 3. VISTA CALENDARIO SEMANAL                         --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            @if($view === 'calendar' && $calendarMode === 'weekly')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- Nav --}}
                <div class="bg-gray-50 px-6 py-4 border-b flex items-center justify-between">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                        ← Anterior
                    </button>
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
                        <button wire:click="today" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition text-sm font-medium">
                            Hoy
                        </button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                            Siguiente →
                        </button>
                    </div>
                </div>
                {{-- Grid semanal --}}
                <div class="grid grid-cols-7 divide-x">
                    @foreach($weekGrid as $day)
                    <div class="min-h-[400px] {{ $day['isToday'] ? 'bg-blue-50' : '' }}">
                        {{-- Encabezado del día --}}
                        <div class="px-2 py-2 border-b text-center {{ $day['isToday'] ? 'bg-blue-100' : 'bg-gray-50' }}">
                            <p class="text-xs font-bold text-gray-500 uppercase">
                                {{ $day['date']->locale('es')->translatedFormat('D') }}
                            </p>
                            <p class="text-lg font-bold {{ $day['isToday'] ? 'text-blue-700' : 'text-gray-800' }}">
                                {{ $day['date']->day }}
                            </p>
                        </div>
                        {{-- Órdenes del día --}}
                        <div class="p-1.5 space-y-1.5">
                            @foreach($day['items'] as $woa)
                            <div wire:click="selectDay('{{ $day['date']->format('Y-m-d') }}')"
                                 class="p-2 rounded-lg border text-xs cursor-pointer hover:shadow transition-all
                                    {{ $woa->kanban_status->value === 'completed' ? 'bg-emerald-50 border-emerald-200' :
                                       ($woa->kanban_status->value === 'in_progress' ? 'bg-amber-50 border-amber-200' : 'bg-blue-50 border-blue-200') }}">
                                <p class="font-mono font-bold text-indigo-600">{{ $woa->workOrder->code }}</p>
                                <p class="text-gray-700 truncate">{{ $woa->workOrder->patient_name }}</p>
                                @if($woa->assignedUser)
                                    <p class="text-gray-400 mt-0.5">👤 {{ Str::before($woa->assignedUser->name, ' ') }}</p>
                                @endif
                                <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                    <div class="bg-indigo-500 h-1 rounded-full" style="width: {{ $woa->progress }}%"></div>
                                </div>
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

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- 4. VISTA CALENDARIO DIARIO                          --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            @if($view === 'calendar' && $calendarMode === 'daily')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- Nav --}}
                <div class="bg-gray-50 px-6 py-4 border-b flex items-center justify-between">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                        ← Anterior
                    </button>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-800 capitalize">
                            {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat('l, d \d\e F Y') }}
                        </h3>
                        <span class="text-sm text-gray-500">Vista Diaria — {{ $area->name }}</span>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition text-sm font-medium">
                            Hoy
                        </button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm font-medium">
                            Siguiente →
                        </button>
                    </div>
                </div>
                {{-- Lista de órdenes del día --}}
                <div class="p-6">
                    @if($daySchedule->count())
                        <div class="space-y-4">
                            @foreach($daySchedule as $woa)
                            <div class="border rounded-xl overflow-hidden hover:shadow-lg transition-all duration-200">
                                {{-- Header de la orden --}}
                                <div class="flex items-center justify-between p-4 bg-gray-50 border-b">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-2 h-12 rounded-full" style="background-color: {{ $area->color }}"></div>
                                        <div>
                                            <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800 text-sm" wire:navigate>
                                                {{ $woa->workOrder->code }}
                                            </a>
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
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                            {{ $woa->kanban_status->value === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                               ($woa->kanban_status->value === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $woa->kanban_status->label() }}
                                        </span>
                                    </div>
                                </div>
                                {{-- Checklist de etapas --}}
                                @if($woa->stages->count())
                                <div class="p-4">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Checklist de Etapas</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($woa->stages as $stage)
                                        <label wire:click="toggleStage({{ $stage->id }})"
                                               class="flex items-center gap-3 p-2 rounded-lg cursor-pointer transition
                                                      {{ $stage->is_completed ? 'bg-emerald-50 border border-emerald-200' : 'bg-gray-50 border border-gray-200 hover:bg-gray-100' }}">
                                            <div class="w-5 h-5 rounded flex items-center justify-center border-2 flex-shrink-0
                                                {{ $stage->is_completed ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300' }}">
                                                @if($stage->is_completed)
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium {{ $stage->is_completed ? 'text-emerald-700 line-through' : 'text-gray-700' }}">
                                                    {{ $stage->areaStage->name }}
                                                </p>
                                                @if($stage->performer)
                                                    <p class="text-xs text-gray-400">✓ {{ $stage->performer->name }}</p>
                                                @endif
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                    {{-- Barra de progreso --}}
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $woa->progress }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-600">{{ $woa->progress }}%</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16 text-gray-300">
                            <p class="text-5xl mb-3">📭</p>
                            <p class="text-lg font-medium">Sin trabajos para esta fecha</p>
                            <p class="text-sm mt-1">Selecciona otro día desde el calendario mensual.</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- 5. VISTA HISTORIAL DE TRABAJOS                      --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            @if($view === 'history')
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- Header con filtros --}}
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            📜 Historial de Trabajos Completados
                        </h3>
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Búsqueda --}}
                            <input wire:model.live.debounce.400ms="historySearch" type="text"
                                   placeholder="Buscar OT, paciente, doctor..."
                                   class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64" />
                            {{-- Rango de fechas --}}
                            <div class="flex items-center gap-2">
                                <input wire:model.live="historyFrom" type="date"
                                       class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                <span class="text-gray-400 text-sm">a</span>
                                <input wire:model.live="historyTo" type="date"
                                       class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Tabla de historial --}}
                @if($historyItems && $historyItems->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código OT</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Doctor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Responsable</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Finalizado</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Duración</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado Global</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($historyItems as $woa)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono text-sm font-bold text-indigo-600 hover:text-indigo-800" wire:navigate>
                                        {{ $woa->workOrder->code }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $woa->workOrder->patient_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $woa->workOrder->doctor_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $woa->assignedUser?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $woa->started_at ? $woa->started_at->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $woa->completed_at ? $woa->completed_at->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs font-medium text-gray-700">
                                    @if($woa->started_at && $woa->completed_at)
                                        @php
                                            $diff = $woa->started_at->diff($woa->completed_at);
                                        @endphp
                                        @if($diff->days > 0)
                                            {{ $diff->days }}d {{ $diff->h }}h
                                        @else
                                            {{ $diff->h }}h {{ $diff->i }}m
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-{{ $woa->workOrder->status->color() }}-100 text-{{ $woa->workOrder->status->color() }}-800">
                                        {{ $woa->workOrder->status->label() }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Paginación --}}
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $historyItems->links() }}
                </div>
                @else
                <div class="text-center py-16 text-gray-300">
                    <p class="text-5xl mb-3">📋</p>
                    <p class="text-lg font-medium">No hay trabajos en el historial</p>
                    <p class="text-sm mt-1">Los trabajos aparecerán aquí al ser finalizados en esta área.</p>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
