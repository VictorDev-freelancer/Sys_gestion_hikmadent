<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📦 {{ __('Inventario — Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('inventory.products') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out" wire:navigate>
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Productos
                </a>
                <a href="{{ route('inventory.movements') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out" wire:navigate>
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    Movimientos
                </a>
                <a href="{{ route('inventory.kardex') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out" wire:navigate>
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Kardex
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Fila 1: KPIs --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-indigo-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Variantes</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-gray-900">{{ $totalProducts }}</span>
                        <span class="ml-2 text-sm text-gray-400">SKUs activos</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-red-500 {{ $lowStockCount > 0 ? 'animate-pulse' : '' }}">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Stock Bajo</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-red-600">{{ $lowStockCount }}</span>
                        <span class="ml-2 text-sm text-red-400">¡Reabastecer!</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-amber-500 {{ $expiringCount > 0 ? 'animate-pulse' : '' }}">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Por Vencer</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-amber-600">{{ $expiringCount }}</span>
                        <span class="ml-2 text-sm text-amber-400">Próx. 30 días</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-xl p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Valor Total</p>
                    <div class="flex items-center mt-2">
                        <span class="text-3xl font-bold text-green-600">S/ {{ number_format($totalValue, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Columna Izquierda: Alertas --}}
                <div class="lg:col-span-1 space-y-6">

                    {{-- Stock Bajo --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-red-600 px-4 py-3 flex justify-between items-center">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">🔴 Stock Bajo</h3>
                            <span class="bg-white text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $lowStockCount }}</span>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[300px] overflow-y-auto">
                            @forelse($lowStockItems as $item)
                                <div class="p-3 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="font-mono text-xs text-indigo-600 font-bold">{{ $item->sku }}</span>
                                            <p class="text-sm text-gray-700">{{ $item->product->name }} — {{ $item->variant_name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-lg font-bold text-red-600">{{ intval($item->current_stock) }}</span>
                                            <p class="text-xs text-gray-400">Mín: {{ intval($item->effective_minimum_stock) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-400 text-sm">
                                    ¡Todo en orden! No hay stock bajo.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Por Vencer --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-amber-600 px-4 py-3 flex justify-between items-center">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">⏰ Por Vencer</h3>
                            <span class="bg-white text-amber-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $expiringCount }}</span>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-[300px] overflow-y-auto">
                            @forelse($expiringItems as $item)
                                <div class="p-3 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="font-mono text-xs text-indigo-600 font-bold">{{ $item->sku }}</span>
                                            <p class="text-sm text-gray-700">{{ $item->variant_name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-bold {{ $item->is_expired ? 'text-red-600' : 'text-amber-600' }}">
                                                {{ $item->expires_at->format('d/m/Y') }}
                                            </span>
                                            <p class="text-xs text-gray-400">Stock: {{ intval($item->current_stock) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-400 text-sm">
                                    Sin productos próximos a vencer.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Categorías --}}
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-gray-800 px-4 py-3">
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">📂 Categorías</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach($categories as $cat)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-700 font-medium">{{ $cat->name }}</span>
                                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $cat->products_count }} productos</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha: Movimientos Recientes --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="border-b px-6 py-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Movimientos Recientes</h3>
                                <p class="text-sm text-gray-500">Últimos 15 movimientos del inventario.</p>
                            </div>
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Stock</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Usuario</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($recentMovements as $mov)
                                        @php
                                            $colors = match($mov->movement_type->value) {
                                                'entry'  => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                                'exit'   => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
                                                'return' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                                default  => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $mov->movement_date->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $colors['bg'] }} {{ $colors['text'] }}">
                                                    {{ $mov->movement_type->label() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-xs text-indigo-600">{{ $mov->productVariant->sku }}</span>
                                                <p class="text-xs text-gray-500">{{ $mov->productVariant->variant_name }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-sm {{ $mov->movement_type->isIncoming() ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $mov->movement_type->isIncoming() ? '+' : '-' }}{{ intval($mov->quantity) }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ intval($mov->stock_after) }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $mov->performer->name ?? 'Sistema' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Sin movimientos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
