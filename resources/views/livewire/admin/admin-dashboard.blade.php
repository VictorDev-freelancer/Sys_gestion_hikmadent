<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Administrativo') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out" wire:navigate>
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar Reporte ETL
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Fila 1: KPIs Rápidos --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-indigo-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Órdenes</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-gray-900">{{ $totalOrders }}</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-yellow-400">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">En Proceso</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-yellow-600">{{ $inProgress }}</span>
                        <span class="ml-2 text-sm text-gray-400">Activas</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-red-500 {{ $delayedOrders->count() > 0 ? 'animate-pulse' : '' }}">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Retrasadas</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-red-600">{{ $delayedOrders->count() }}</span>
                        <span class="ml-2 text-sm text-red-400">¡Fuera de plazo!</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Completadas</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-green-600">{{ $completed }}</span>
                    </div>
                </div>
            </div>

            {{-- ═══ FULLCALENDAR — Calendario de Órdenes Pendientes ═══ --}}
            <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 flex items-center justify-between" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border-bottom:2px solid #4338ca">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📅</span>
                        <div>
                            <h3 class="font-bold text-white text-lg">Calendario de Órdenes Pendientes</h3>
                            <p class="text-indigo-200 text-xs">Vista global de todas las OTs activas por fecha de entrega</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-white/80">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:#3b82f6"></span> Registrada
                        <span class="w-3 h-3 rounded-full inline-block ml-2" style="background:#f59e0b"></span> En Proceso
                        <span class="w-3 h-3 rounded-full inline-block ml-2" style="background:#ef4444"></span> Retrasada
                    </div>
                </div>
                <div class="p-4" wire:ignore>
                    <div id="admin-calendar" x-data="fullcalendar(@js($calendarEvents))"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Columna Izquierda: Alertas y Cuellos de botella --}}
                <div class="lg:col-span-1 space-y-6">
                    
                    {{-- Órdenes Urgentes/Retrasadas --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-red-600 px-4 py-3 flex justify-between items-center">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">Centro de Alertas</h3>
                            <span class="bg-white text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $delayedOrders->count() + $urgentOrders->count() }}</span>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[300px] overflow-y-auto">
                            @forelse($delayedOrders->concat($urgentOrders)->unique('id') as $alertOrder)
                                <div class="p-3 hover:bg-gray-50 transition">
                                    <div class="flex justify-between">
                                        <a href="{{ route('work-orders.show', $alertOrder) }}" class="font-mono font-bold text-indigo-600 text-sm hover:underline" wire:navigate>{{ $alertOrder->code }}</a>
                                        @if($alertOrder->delivery_date && $alertOrder->delivery_date->isPast())
                                            <span class="text-xs font-bold text-red-600 bg-red-100 px-2 rounded">Retrasada</span>
                                        @else
                                            <span class="text-xs font-bold text-orange-600 bg-orange-100 px-2 rounded">Urgente</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">{{ $alertOrder->patient_name }} — Dr. {{ $alertOrder->doctor_name }}</p>
                                    @if($alertOrder->delivery_date)
                                        <p class="text-[10px] text-gray-400 mt-1">Entrega: {{ $alertOrder->delivery_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-400 text-sm">
                                    No hay alertas pendientes. ¡Todo en orden!
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Cuellos de Botella --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-gray-800 px-4 py-3">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">Carga por Área</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            @foreach($bottlenecks as $area)
                                @php 
                                    $max = $bottlenecks->max('work_order_areas_count') ?: 1;
                                    $percent = ($area->work_order_areas_count / $max) * 100;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-xs font-medium text-gray-700 mb-1">
                                        <span>{{ $area->name }}</span>
                                        <span class="{{ $percent > 80 ? 'text-red-600 font-bold' : '' }}">{{ $area->work_order_areas_count }} OT activas</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%; background-color: {{ $area->color }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha: Analytics y Logs --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Distribución de Trabajos --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="border-b px-6 py-4">
                            <h3 class="font-bold text-gray-800 text-lg">Distribución de Solicitudes</h3>
                            <p class="text-sm text-gray-500">Tipos de trabajo protésico más demandados.</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @forelse($prostheticDist as $dist)
                                    <div>
                                        <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                                            <span>{{ $dist['label'] }}</span>
                                            <span>{{ $dist['total'] }}</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-4">
                                            @php $maxDist = $prostheticDist->max('total') ?: 1; @endphp
                                            <div class="bg-indigo-500 h-4 rounded-full" style="width: {{ ($dist['total'] / $maxDist) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-400 text-sm">Sin datos suficientes.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Flujo en Vivo (Trazabilidad) --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="border-b px-6 py-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Radar en Vivo</h3>
                                <p class="text-sm text-gray-500">Últimos 10 movimientos registrados en el laboratorio.</p>
                            </div>
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                        </div>
                        <div class="p-0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($recentLogs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm">
                                                <a href="{{ route('work-orders.show', $log->workOrder) }}" class="font-mono text-indigo-600 font-bold hover:underline" wire:navigate>{{ $log->workOrder->code }}</a>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                <span class="font-medium">{{ $log->action_label }}</span>
                                                @if($log->fromArea) <span class="text-xs text-gray-400 mx-1">de</span> <span class="text-xs border px-1 rounded">{{ $log->fromArea->name }}</span> @endif
                                                @if($log->toArea) <span class="text-xs text-gray-400 mx-1">→</span> <span class="text-xs border px-1 rounded">{{ $log->toArea->name }}</span> @endif
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500">
                                                {{ $log->performer->name ?? 'Sistema' }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-400 text-right">
                                                {{ $log->created_at->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Fila 3: Historial Global de Trabajos Realizados --}}
            <div class="bg-white rounded-lg shadow-xl overflow-hidden mt-6">
                <div class="px-6 py-4 flex items-center justify-between" style="background:#f5f3ff; border-bottom:1px solid #e5e7eb">
                    <h3 class="font-bold text-base flex items-center gap-2" style="color:#5b21b6">
                        📜 Historial Global de Trabajos Realizados
                    </h3>
                    <span class="text-xs font-bold px-3 py-1 rounded-full" style="background:#ddd6fe;color:#5b21b6">Últimas 50 entregas de todas las áreas</span>
                </div>

                @if($globalHistoryItems->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y" style="border-color:#e5e7eb">
                        <thead>
                            <tr style="background:#faf5ff">
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Código OT</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Área</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Paciente</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Doctor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Responsable</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Inicio de Trabajo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Confirmación de Entrega</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color:#6b7280">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y" style="border-color:#f3f4f6">
                            @foreach($globalHistoryItems as $woa)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <a href="{{ route('work-orders.show', $woa->workOrder) }}" class="font-mono text-sm font-bold block" style="color:#4f46e5" wire:navigate>{{ $woa->workOrder->code }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold border" style="background:{{ $woa->area->color }}15; border-color:{{ $woa->area->color }}50; color:{{ $woa->area->color }}">{{ $woa->area->name }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $woa->workOrder->patient_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">Dr. {{ $woa->workOrder->doctor_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $woa->assignedUser?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $woa->started_at ? $woa->started_at->format('d/m/Y H:i') : '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800 font-bold bg-green-50/30">{{ $woa->completed_at ? $woa->completed_at->format('d/m/Y H:i') : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 flex items-center justify-center w-max rounded-full text-xs font-bold" style="background:#d1fae5;color:#065f46">✅ Finalizado</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12 text-gray-300 border-t">
                    <p class="text-4xl mb-3">📋</p>
                    <p class="text-lg font-medium">Sin entregas globales registradas</p>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- FullCalendar Estilos --}}
    <style>
        #admin-calendar .fc { font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; }
        #admin-calendar .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; color: #1f2937 !important; text-transform: capitalize; }
        #admin-calendar .fc-button { background: #4f46e5 !important; border-color: #4338ca !important; font-weight: 600 !important; font-size: 0.8rem !important; padding: 6px 14px !important; border-radius: 8px !important; }
        #admin-calendar .fc-button:hover { background: #4338ca !important; transform: translateY(-1px); }
        #admin-calendar .fc-button-active { background: #3730a3 !important; }
        #admin-calendar .fc-today-button { background: #059669 !important; border-color: #047857 !important; }
        #admin-calendar .fc-day-today { background: #eff6ff !important; }
        #admin-calendar .fc-daygrid-day-number { font-weight: 600; color: #374151; padding: 6px 10px !important; }
        #admin-calendar .fc-day-today .fc-daygrid-day-number { background: #4f46e5; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; }
        #admin-calendar .fc-event { border-radius: 6px !important; padding: 2px 6px !important; font-size: 0.72rem !important; font-weight: 600 !important; cursor: pointer !important; }
        #admin-calendar .fc-event:hover { transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important; }
        #admin-calendar .fc-col-header-cell { background: #f9fafb; font-weight: 700 !important; text-transform: uppercase !important; font-size: 0.7rem !important; color: #6b7280 !important; }
        .fc-event-tooltip { position: fixed; z-index: 9999; background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.12); pointer-events: none; min-width: 240px; max-width: 300px; }
    </style>
</div>

