<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">📊 {{ __('Kardex de Inventario') }}</h2>
            <a href="{{ route('inventory.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium" wire:navigate>← Volver al Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h3 class="font-bold text-gray-800 mb-4">Seleccionar Producto</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                        <select wire:model.live="selectedProductId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccionar producto...</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->code }} — {{ $prod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Variante</label>
                        <select wire:model.live="selectedVariantId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" {{ !$selectedProductId ? 'disabled' : '' }}>
                            <option value="">Seleccionar variante...</option>
                            @foreach($variants as $var)
                                <option value="{{ $var->id }}">{{ $var->sku }} — {{ $var->variant_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" wire:model.live="dateFrom" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" wire:model.live="dateTo" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                @if($selectedVariant)
                    <div class="mt-4 flex items-center justify-between bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                        <div>
                            <span class="font-mono text-indigo-600 font-bold">{{ $selectedVariant->sku }}</span>
                            <span class="text-gray-700 ml-2">{{ $selectedVariant->product->name }} — {{ $selectedVariant->variant_name }}</span>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 uppercase">Stock Actual</p>
                                <p class="text-2xl font-bold {{ $selectedVariant->is_low_stock ? 'text-red-600' : 'text-gray-900' }}">{{ intval($selectedVariant->current_stock) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 uppercase">Costo Prom.</p>
                                <p class="text-lg font-bold text-gray-700">{{ $selectedVariant->cost_price ? 'S/ ' . number_format($selectedVariant->cost_price, 2) : '—' }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 uppercase">Valor Total</p>
                                <p class="text-lg font-bold text-green-600">S/ {{ number_format(intval($selectedVariant->current_stock) * ($selectedVariant->cost_price ?? 0), 2) }}</p>
                            </div>
                            <button wire:click="reconcile" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg text-xs transition">🔍 Reconciliar</button>
                        </div>
                    </div>
                @endif

                {{-- Reconciliación --}}
                @if($reconciliation)
                    <div class="mt-4 p-4 rounded-lg border {{ $reconciliation['is_synced'] ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300' }}">
                        <h4 class="font-bold text-sm {{ $reconciliation['is_synced'] ? 'text-green-700' : 'text-red-700' }} mb-2">
                            {{ $reconciliation['is_synced'] ? '✅ Stock sincronizado correctamente' : '❌ Desincronización detectada' }}
                        </h4>
                        <div class="grid grid-cols-5 gap-4 text-sm">
                            <div><p class="text-gray-500">Stock Cacheado</p><p class="font-bold">{{ $reconciliation['cached_stock'] }}</p></div>
                            <div><p class="text-gray-500">Stock Calculado</p><p class="font-bold">{{ $reconciliation['calculated_stock'] }}</p></div>
                            <div><p class="text-gray-500">Total Entradas</p><p class="font-bold text-green-600">+{{ $reconciliation['entries'] }}</p></div>
                            <div><p class="text-gray-500">Total Salidas</p><p class="font-bold text-red-600">-{{ $reconciliation['exits'] }}</p></div>
                            <div><p class="text-gray-500">Ajustes Netos</p><p class="font-bold text-yellow-600">+{{ $reconciliation['adjustments_add'] }} / -{{ $reconciliation['adjustments_sub'] }}</p></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tabla Kardex --}}
            @if($selectedVariantId)
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="font-bold text-gray-800">📋 Kardex — {{ $movements->count() }} movimientos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Motivo</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Entrada</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Salida</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Costo Unit.</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Proveedor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Notas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($movements as $mov)
                                @php
                                    $isIn = $mov->movement_type->isIncoming();
                                    $rowColor = match($mov->movement_type->value) {
                                        'entry'  => 'bg-green-50/50',
                                        'exit'   => 'bg-red-50/50',
                                        'return' => 'bg-blue-50/50',
                                        default  => 'bg-yellow-50/50',
                                    };
                                    $colors = match($mov->movement_type->value) {
                                        'entry'  => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                        'exit'   => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
                                        'return' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                        default  => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                    };
                                @endphp
                                <tr class="{{ $rowColor }} hover:bg-gray-100 transition">
                                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $mov->movement_date->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $colors['bg'] }} {{ $colors['text'] }}">{{ $mov->movement_type->label() }}</span></td>
                                    <td class="px-4 py-3 text-xs text-gray-600">{{ $mov->reason->label() }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-sm text-green-600">{{ $isIn ? intval($mov->quantity) : '' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-sm text-red-600">{{ !$isIn ? intval($mov->quantity) : '' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-sm text-gray-900 bg-gray-100/50">{{ intval($mov->stock_after) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $mov->unit_cost ? 'S/ ' . number_format($mov->unit_cost, 2) : '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $mov->supplier?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $mov->performer->name ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-400 max-w-[150px] truncate" title="{{ $mov->notes }}">{{ $mov->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">Sin movimientos para esta variante en el rango seleccionado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @else
                <div class="bg-white rounded-lg shadow-xl p-12 text-center">
                    <p class="text-4xl mb-3">📊</p>
                    <p class="text-lg font-medium text-gray-400">Selecciona un producto y variante para ver su Kardex</p>
                </div>
            @endif

        </div>
    </div>
</div>
