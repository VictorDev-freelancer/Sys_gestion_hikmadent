<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Administrativo') }}
            </h2>
            <a href="{{ route('admin.reports') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out" wire:navigate>
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Reporte ETL
            </a>
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
        </div>
    </div>
</div>
