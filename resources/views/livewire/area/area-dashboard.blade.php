<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $area->color }}"></div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $area->name }}</h2>
            </div>
            <div class="flex space-x-2">
                <button wire:click="setView('kanban')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'kanban' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Kanban
                </button>
                <button wire:click="setCalendarMode('monthly')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'calendar' && $calendarMode === 'monthly' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Mensual
                </button>
                <button wire:click="setCalendarMode('weekly')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'calendar' && $calendarMode === 'weekly' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Semanal
                </button>
                <button wire:click="setCalendarMode('daily')" class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $view === 'calendar' && $calendarMode === 'daily' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Diario
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Estadísticas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4" style="border-color: {{ $area->color }}">
                    <p class="text-sm text-gray-500">Total Trabajos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500">Inicio</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500">En Proceso</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Finalizado</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                </div>
            </div>

            {{-- ============================================= --}}
            {{-- VISTA KANBAN                                    --}}
            {{-- ============================================= --}}
            @if($view === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(['pending' => ['Inicio', 'blue', 'clock'], 'in_progress' => ['En Proceso', 'yellow', 'arrow-path'], 'completed' => ['Finalizado', 'green', 'check-circle']] as $status => [$label, $statusColor, $icon])
                <div class="bg-{{ $statusColor }}-50 rounded-lg border-2 border-{{ $statusColor }}-200">
                    <div class="px-4 py-3 border-b border-{{ $statusColor }}-200 flex items-center justify-between">
                        <h3 class="font-bold text-{{ $statusColor }}-800 flex items-center">
                            <span class="w-3 h-3 rounded-full bg-{{ $statusColor }}-500 mr-2"></span>
                            {{ $label }}
                        </h3>
                        <span class="bg-{{ $statusColor }}-200 text-{{ $statusColor }}-800 text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $kanbanItems[$status]->count() }}
                        </span>
                    </div>
                    <div class="p-3 space-y-3 min-h-[200px]">
                        @foreach($kanbanItems[$status] as $woa)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 hover:shadow-md transition">
                            {{-- Header de la card --}}
                            <div class="flex justify-between items-start mb-2">
                                <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="text-sm font-mono font-bold text-indigo-600 hover:text-indigo-800" wire:navigate>
                                    {{ $woa->workOrder->code }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $woa->workOrder->priority->color() }}-100 text-{{ $woa->workOrder->priority->color() }}-800">
                                    {{ $woa->workOrder->priority->label() }}
                                </span>
                            </div>

                            {{-- Datos del paciente --}}
                            <p class="text-sm font-medium text-gray-800">{{ $woa->workOrder->patient_name }}</p>
                            <p class="text-xs text-gray-500">Dr. {{ $woa->workOrder->doctor_name }}</p>

                            {{-- Progreso --}}
                            <div class="mt-2">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Progreso</span>
                                    <span>{{ $woa->progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-{{ $statusColor }}-500 h-1.5 rounded-full transition-all" style="width: {{ $woa->progress }}%"></div>
                                </div>
                            </div>

                            {{-- Responsables --}}
                            <div class="mt-2 flex items-center justify-between">
                                @if($woa->assignedUser)
                                    <span class="text-xs text-gray-500">👤 {{ $woa->assignedUser->name }}</span>
                                @endif
                                @if($woa->supervisor)
                                    <span class="text-xs text-gray-500">🔬 {{ $woa->supervisor->name }}</span>
                                @endif
                            </div>

                            {{-- Botones de movimiento Kanban --}}
                            <div class="mt-2 flex justify-between">
                                @if($status !== 'pending')
                                    <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'pending')" class="text-xs text-blue-600 hover:text-blue-800">← Inicio</button>
                                @else
                                    <span></span>
                                @endif
                                @if($status !== 'in_progress')
                                    <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'in_progress')" class="text-xs text-yellow-600 hover:text-yellow-800">En Proceso</button>
                                @endif
                                @if($status !== 'completed')
                                    <button wire:click="moveToKanbanColumn({{ $woa->id }}, 'completed')" class="text-xs text-green-600 hover:text-green-800">Finalizar →</button>
                                @else
                                    <span></span>
                                @endif
                            </div>
                        </div>
                        @endforeach

                        @if($kanbanItems[$status]->isEmpty())
                            <div class="text-center py-8 text-{{ $statusColor }}-300">
                                <p class="text-sm">Sin trabajos</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ============================================= --}}
            {{-- VISTA CALENDARIO                                --}}
            {{-- ============================================= --}}
            @if($view === 'calendar')
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                {{-- Navegación del Calendario --}}
                <div class="bg-gray-50 px-6 py-4 border-b flex items-center justify-between">
                    <button wire:click="previousPeriod" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                        ← Anterior
                    </button>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->translatedFormat($calendarMode === 'daily' ? 'l, d F Y' : ($calendarMode === 'weekly' ? '\\S\\e\\m\\a\\n\\a d\\e\\l d M' : 'F Y')) }}
                        </h3>
                        <span class="text-sm text-gray-500">Vista {{ $calendarMode === 'monthly' ? 'Mensual' : ($calendarMode === 'weekly' ? 'Semanal' : 'Diaria') }}</span>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="today" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition text-sm font-medium">
                            Hoy
                        </button>
                        <button wire:click="nextPeriod" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                            Siguiente →
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    @if($calendarMode === 'daily')
                        {{-- Vista Diaria: Lista completa --}}
                        @if(isset($calendarItems['items']) && $calendarItems['items']->count())
                            <div class="space-y-3">
                                @foreach($calendarItems['items'] as $woa)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:shadow transition">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-2 h-10 rounded-full" style="background-color: {{ $area->color }}"></div>
                                        <div>
                                            <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800 text-sm" wire:navigate>{{ $woa->workOrder->code }}</a>
                                            <p class="text-sm text-gray-800">{{ $woa->workOrder->patient_name }} — Dr. {{ $woa->workOrder->doctor_name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        @if($woa->assignedUser)<span class="text-xs text-gray-500">{{ $woa->assignedUser->name }}</span>@endif
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $woa->kanban_status->color() }}-100 text-{{ $woa->kanban_status->color() }}-800">
                                            {{ $woa->kanban_status->label() }}
                                        </span>
                                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $woa->progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center text-gray-400 py-12">Sin trabajos para esta fecha.</p>
                        @endif
                    @else
                        {{-- Vista Mensual/Semanal: Agrupado por fecha --}}
                        @if(count($calendarItems))
                            <div class="space-y-4">
                                @foreach($calendarItems as $date => $items)
                                    <div class="border rounded-lg overflow-hidden">
                                        <div class="bg-gray-100 px-4 py-2 font-medium text-sm text-gray-700">
                                            {{ \Carbon\Carbon::parse($date)->locale('es')->translatedFormat('l, d \\d\\e F') }}
                                            <span class="text-gray-400 ml-2">({{ count($items) }} {{ count($items) === 1 ? 'trabajo' : 'trabajos' }})</span>
                                        </div>
                                        <div class="divide-y">
                                            @foreach($items as $woa)
                                            <div class="px-4 py-2 flex items-center justify-between hover:bg-gray-50 text-sm">
                                                <div class="flex items-center space-x-3">
                                                    <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono text-indigo-600 hover:text-indigo-800" wire:navigate>{{ $woa->workOrder->code }}</a>
                                                    <span class="text-gray-700">{{ $woa->workOrder->patient_name }}</span>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $woa->kanban_status->color() }}-100 text-{{ $woa->kanban_status->color() }}-800">{{ $woa->kanban_status->label() }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center text-gray-400 py-12">Sin trabajos para este período.</p>
                        @endif
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
