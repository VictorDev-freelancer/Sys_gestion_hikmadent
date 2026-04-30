<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🔄 {{ __('Movimientos de Inventario') }}</h2>
            <a href="{{ route('inventory.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium" wire:navigate>← Volver al Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow">{{ session('message') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow">{{ session('error') }}</div>
            @endif

            {{-- Botones de Acción --}}
            <div class="flex flex-wrap gap-3">
                <button wire:click="openMovementModal('entry')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition flex items-center gap-2">
                    📥 Registrar Entrada
                </button>
                <button wire:click="openMovementModal('work_order')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition flex items-center gap-2">
                    📤 Consumo por OT
                </button>
                <button wire:click="openMovementModal('adjustment')" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition flex items-center gap-2">
                    ⚙️ Ajuste de Inventario
                </button>
                <button wire:click="openMovementModal('return')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg text-sm transition flex items-center gap-2">
                    ↩️ Devolución de OT
                </button>
            </div>

            {{-- Tabla de Movimientos --}}
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 flex justify-between items-center border-b">
                    <div class="flex items-center gap-4">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por SKU..." class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64">
                        <select wire:model.live="typeFilter" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos los tipos</option>
                            @foreach($movementTypes as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Motivo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Producto / SKU</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Antes</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Después</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Costo Unit.</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Proveedor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Notas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($movements as $mov)
                                @php
                                    $colors = match($mov->movement_type->value) {
                                        'entry'  => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                        'exit'   => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
                                        'return' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                        default  => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $mov->movement_date->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $colors['bg'] }} {{ $colors['text'] }}">{{ $mov->movement_type->label() }}</span></td>
                                    <td class="px-4 py-3 text-xs text-gray-600">{{ $mov->reason->label() }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-indigo-600 font-bold">{{ $mov->productVariant->sku }}</span>
                                        <p class="text-xs text-gray-400">{{ $mov->productVariant->product->name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-sm {{ $mov->movement_type->isIncoming() ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $mov->movement_type->isIncoming() ? '+' : '-' }}{{ intval($mov->quantity) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500">{{ intval($mov->stock_before) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700 font-bold">{{ intval($mov->stock_after) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $mov->unit_cost ? 'S/ ' . number_format($mov->unit_cost, 2) : '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $mov->supplier?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $mov->performer->name ?? 'Sistema' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-400 max-w-[150px] truncate">{{ $mov->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Sin movimientos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t">{{ $movements->links() }}</div>
            </div>
        </div>
    </div>

    {{-- MODAL: Nuevo Movimiento --}}
    @if($showMovementModal)
    <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                @switch($movementAction)
                    @case('entry') 📥 Registrar Entrada @break
                    @case('work_order') 📤 Consumo por Orden de Trabajo @break
                    @case('adjustment') ⚙️ Ajuste de Inventario @break
                    @case('return') ↩️ Devolución de Orden @break
                @endswitch
            </h3>
            <div class="space-y-4">
                {{-- Variante --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto / Variante *</label>
                    <select wire:model="selectedVariantId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccionar variante...</option>
                        @foreach($variants as $var)
                            <option value="{{ $var->id }}">{{ $var->sku }} — {{ $var->product->name }} — {{ $var->variant_name }} (Stock: {{ intval($var->current_stock) }})</option>
                        @endforeach
                    </select>
                    @error('selectedVariantId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Cantidad --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                    <input type="number" step="1" min="1" wire:model="movementQuantity" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('movementQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Campos específicos por tipo --}}
                @if($movementAction === 'entry')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                            <select wire:model="movementSupplierId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Sin proveedor</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Costo Unitario (S/)</label>
                            <input type="number" step="0.01" wire:model="movementUnitCost" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                @endif

                @if(in_array($movementAction, ['work_order', 'return']))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Orden de Trabajo *</label>
                        <select wire:model="movementWorkOrderId" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccionar OT...</option>
                            @foreach($workOrders as $wo)
                                <option value="{{ $wo->id }}">{{ $wo->code }} — {{ $wo->patient_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($movementAction === 'adjustment')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <select wire:model="adjustmentIsPositive" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="1">➕ Positivo (agregar)</option>
                                <option value="0">➖ Negativo (restar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razón</label>
                            <select wire:model="adjustmentReason" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($adjustReasons as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas / Observaciones</label>
                    <textarea wire:model="movementNotes" rows="2" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showMovementModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                <button wire:click="saveMovement" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Registrar Movimiento</button>
            </div>
        </div>
    </div>
    @endif
</div>
