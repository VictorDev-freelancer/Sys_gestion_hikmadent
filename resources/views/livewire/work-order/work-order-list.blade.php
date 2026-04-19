<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Órdenes de Trabajo') }}
            </h2>
            <a href="{{ route('work-orders.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out" wire:navigate>
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Orden
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            {{-- Estadísticas Rápidas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500">En Progreso</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $workOrders->where('status.value', 'in_progress')->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500">Completadas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $workOrders->where('status.value', 'completed')->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500">Urgentes</p>
                    <p class="text-2xl font-bold text-red-600">{{ $workOrders->where('priority.value', 'urgent')->count() }}</p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="bg-white rounded-lg shadow mb-6 p-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Búsqueda --}}
                    <div class="md:col-span-2">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por código, paciente, doctor..." class="pl-10 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" id="search-orders">
                        </div>
                    </div>

                    {{-- Estado --}}
                    <select wire:model.live="filterStatus" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" id="filter-status">
                        <option value="">Todos los estados</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    {{-- Área --}}
                    <select wire:model.live="filterArea" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" id="filter-area">
                        <option value="">Todas las áreas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>

                    {{-- Prioridad --}}
                    <select wire:model.live="filterPriority" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" id="filter-priority">
                        <option value="">Todas las prioridades</option>
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($search || $filterStatus || $filterArea || $filterPriority)
                    <div class="mt-3">
                        <button wire:click="clearFilters" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpiar filtros
                        </button>
                    </div>
                @endif
            </div>

            {{-- Tabla de Órdenes --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('code')">
                                    Código
                                    @if($sortBy === 'code')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Protésico</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área Actual</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('priority')">
                                    Prioridad
                                    @if($sortBy === 'priority')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('created_at')">
                                    Fecha
                                    @if($sortBy === 'created_at')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($workOrders as $order)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('work-orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 font-mono font-bold text-sm" wire:navigate>
                                        {{ $order->code }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->patient_name }}</div>
                                    @if($order->patient_age)
                                        <div class="text-xs text-gray-500">{{ $order->patient_age }} años</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->doctor_name }}</div>
                                    @if($order->clinic_name)
                                        <div class="text-xs text-gray-500">{{ $order->clinic_name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm text-gray-700">{{ $order->prosthetic_type->label() }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($order->currentArea)
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full text-white" style="background-color: {{ $order->currentArea->color }}">
                                            {{ $order->currentArea->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order->status->color() }}-100 text-{{ $order->status->color() }}-800">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order->priority->color() }}-100 text-{{ $order->priority->color() }}-800">
                                        {{ $order->priority->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('work-orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 border border-indigo-200 rounded px-3 py-1" wire:navigate>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium">No se encontraron órdenes de trabajo</p>
                                    <p class="mt-1 text-sm">Crea una nueva orden para comenzar.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($workOrders->hasPages())
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $workOrders->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
