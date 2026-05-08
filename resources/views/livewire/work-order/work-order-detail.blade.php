<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Orden {{ $workOrder->code }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $workOrder->patient_name }} — Dr.(a) {{ $workOrder->doctor_name }}</p>
            </div>
            <div class="flex space-x-2">
                <span class="px-3 py-1 rounded-full text-sm font-bold bg-{{ $workOrder->status->color() }}-100 text-{{ $workOrder->status->color() }}-800">
                    {{ $workOrder->status->label() }}
                </span>
                <span class="px-3 py-1 rounded-full text-sm font-bold bg-{{ $workOrder->priority->color() }}-100 text-{{ $workOrder->priority->color() }}-800">
                    {{ $workOrder->priority->label() }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- COLUMNA IZQUIERDA: Datos + Acciones --}}
                <div class="lg:col-span-1 space-y-6">

                    {{-- Datos del Paciente --}}
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="bg-indigo-600 px-4 py-3">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Ficha de Trabajo</h3>
                        </div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Código:</span><span class="font-mono font-bold">{{ $workOrder->code }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Paciente:</span><span class="font-medium">{{ $workOrder->patient_name }}</span></div>
                            @if($workOrder->patient_age)<div class="flex justify-between"><span class="text-gray-500">Edad:</span><span>{{ $workOrder->patient_age }} años</span></div>@endif
                            <div class="flex justify-between"><span class="text-gray-500">Doctor:</span><span>{{ $workOrder->doctor_name }}</span></div>
                            @if($workOrder->clinic_name)<div class="flex justify-between"><span class="text-gray-500">Consultorio:</span><span>{{ $workOrder->clinic_name }}</span></div>@endif
                            <hr>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tipo de Cliente:</span>
                                <span class="font-medium">{{ $workOrder->client_type === 'student' ? 'Estudiante' : 'Persona Natural / Clínica' }}</span>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-gray-500">Servicio:</span>
                                <span class="font-medium">{{ $workOrder->catalogItem ? $workOrder->catalogItem->name : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between mt-1"><span class="text-gray-500">Cantidad:</span><span>{{ $workOrder->quantity }}</span></div>
                            <div class="flex justify-between mt-1"><span class="text-gray-500">P. Unitario:</span><span>S/ {{ number_format($workOrder->unit_price, 2) }}</span></div>
                            <div class="flex justify-between mt-1 border-t pt-1 border-gray-100">
                                <span class="text-gray-700 font-bold">Total a Facturar:</span>
                                <span class="font-bold text-indigo-700">S/ {{ number_format($workOrder->total_price, 2) }}</span>
                            </div>
                            @if($workOrder->color)<div class="flex justify-between"><span class="text-gray-500">Color:</span><span>{{ $workOrder->color }}</span></div>@endif
                            @if($workOrder->specifications)
                            <div>
                                <span class="text-gray-500 block mb-1">Especificaciones:</span>
                                <p class="bg-gray-50 p-2 rounded text-xs">{{ $workOrder->specifications }}</p>
                            </div>
                            @endif
                            <hr>
                            @if($workOrder->order_date)<div class="flex justify-between"><span class="text-gray-500">Fecha Orden:</span><span>{{ $workOrder->order_date->format('d/m/Y') }}</span></div>@endif
                            @if($workOrder->technical_send_date)<div class="flex justify-between"><span class="text-gray-500">Envío Técnico:</span><span>{{ $workOrder->technical_send_date->format('d/m/Y') }}</span></div>@endif
                            @if($workOrder->delivery_date)<div class="flex justify-between"><span class="text-gray-500">Entrega:</span><span class="font-bold text-red-600">{{ $workOrder->delivery_date->format('d/m/Y') }}</span></div>@endif
                            @if($workOrder->assignedTpd)<div class="flex justify-between"><span class="text-gray-500">TPD:</span><span>{{ $workOrder->assignedTpd->name }}</span></div>@endif
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="bg-gray-800 px-4 py-3">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Acciones</h3>
                        </div>
                        <div class="p-4 space-y-2">
                            @hasanyrole('Super usuario|Administración')
                            @if($workOrder->status->value === 'draft')
                                <button wire:click="registerOrder" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                                    Registrar Orden
                                </button>
                            @endif
                            @endhasanyrole

                            <a href="{{ route('work-orders.print', $workOrder) }}" target="_blank" class="w-full flex items-center justify-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition shadow-sm">
                                <span class="mr-2">🖨️</span> Imprimir Orden
                            </a>

                            @if(in_array($workOrder->status->value, ['registered', 'in_progress']))
                                <button wire:click="openTransferModal" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                                    Transferir a Área
                                </button>
                            @endif

                            @hasanyrole('Super usuario|Administración')
                            @if($workOrder->status->value === 'in_progress')
                                <button wire:click="completeOrder" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition" onclick="confirm('¿Confirmar que la orden está completada?') || event.stopImmediatePropagation()">
                                    Marcar Completada
                                </button>
                            @endif

                            @if($workOrder->status->value === 'completed')
                                <button wire:click="deliverOrder" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition">
                                    Confirmar Entrega
                                </button>
                            @endif

                            @if(in_array($workOrder->status->value, ['draft', 'registered', 'in_progress']))
                                <button wire:click="cancelOrder" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition" onclick="confirm('¿Estás seguro de cancelar esta orden?') || event.stopImmediatePropagation()">
                                    Cancelar Orden
                                </button>
                            @endif
                            @endhasanyrole

                            @hasanyrole('Super usuario|Administración')
                            <a href="{{ route('work-orders.index') }}" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium text-center block hover:bg-gray-50 transition" wire:navigate>
                                Volver al Listado
                            </a>
                            @else
                            <button onclick="window.history.back()" class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium transition hover:bg-gray-50">
                                ← Volver
                            </button>
                            @endhasanyrole
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: Recorrido + Trazabilidad --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Ruta Planificada (Workflow) --}}
                    @if(!empty($workOrder->planned_route))
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-pink-100">
                        <div class="bg-pink-600 px-4 py-3 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Ruta Planificada (Workflow)
                            </h3>
                        </div>
                        <div class="p-4 bg-pink-50/30">
                            <div class="flex flex-wrap gap-2 items-center">
                                @php
                                    $currentIndex = -1;
                                    $isCurrentKanbanCompleted = false;
                                    
                                    if ($workOrder->status->value !== 'completed') {
                                        foreach($workOrder->planned_route as $i => $s) {
                                            if ($s['area_id'] == $workOrder->current_area_id) {
                                                $currentIndex = $i;
                                                break;
                                            }
                                        }
                                        
                                        $currentWoa = $workOrder->workOrderAreas->where('area_id', $workOrder->current_area_id)->first();
                                        if ($currentWoa && $currentWoa->kanban_status->value === 'completed') {
                                            $isCurrentKanbanCompleted = true;
                                        }
                                    }
                                @endphp
                                @foreach($workOrder->planned_route as $index => $step)
                                    @php
                                        $stepArea = \App\Models\Area::find($step['area_id']);
                                        $stepTech = !empty($step['technician_id']) ? \App\Models\User::find($step['technician_id']) : null;
                                        
                                        $isCurrent = $workOrder->current_area_id == $step['area_id'] && $workOrder->status->value !== 'completed';
                                        
                                        $isCompleted = $workOrder->status->value === 'completed' 
                                                       || ($currentIndex !== -1 && $index < $currentIndex) 
                                                       || ($isCurrent && $isCurrentKanbanCompleted);
                                        
                                        $bgClass = 'bg-white border-gray-200';
                                        if ($isCompleted) {
                                            $bgClass = 'bg-green-100 border-green-400';
                                        } elseif ($isCurrent) {
                                            $bgClass = 'bg-pink-100 border-pink-400 shadow-sm ring-2 ring-pink-200';
                                        }
                                    @endphp
                                    @if($stepArea)
                                        <div class="flex items-center">
                                            <div class="px-3 py-2 rounded border {{ $bgClass }}">
                                                <div class="text-xs font-bold {{ $isCompleted ? 'text-green-600' : 'text-gray-500' }} mb-0.5">
                                                    PASO {{ $index + 1 }}
                                                    @if($isCompleted)
                                                        <svg class="inline w-3 h-3 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="font-bold {{ $isCompleted ? 'text-green-800' : ($isCurrent ? 'text-pink-700' : 'text-gray-800') }}">
                                                    {{ $stepArea->name }}
                                                </div>
                                                @if($stepTech)
                                                    <div class="text-xs text-gray-500 mt-1">Técnico: {{ $stepTech->name }}</div>
                                                @endif
                                            </div>
                                            @if(!$loop->last)
                                                <svg class="h-5 w-5 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Recorrido por Áreas (Checklist) --}}
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="bg-violet-600 px-4 py-3">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Recorrido por Áreas — Checklist (Más reciente arriba)</h3>
                        </div>
                        <div class="p-4">
                            @forelse($workOrder->workOrderAreas as $woa)
                                <div class="mb-4 border rounded-lg overflow-hidden">
                                    {{-- Header del Área --}}
                                    <div class="px-4 py-3 flex items-center justify-between" style="background-color: {{ $woa->area->color }}15; border-left: 4px solid {{ $woa->area->color }};">
                                        <div>
                                            <span class="font-bold text-gray-800">{{ $woa->area->name }}</span>
                                            @if($woa->assignedUser)
                                                <span class="text-xs text-gray-500 ml-2">Técnico: {{ $woa->assignedUser->name }}</span>
                                            @endif
                                            @if($woa->supervisor)
                                                <span class="text-xs text-gray-500 ml-2">| Supervisor: {{ $woa->supervisor->name }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $woa->kanban_status->color() }}-100 text-{{ $woa->kanban_status->color() }}-800">
                                                {{ $woa->kanban_status->label() }}
                                            </span>
                                            {{-- Barra de progreso --}}
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $woa->progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $woa->progress }}%</span>
                                        </div>
                                    </div>
                                    {{-- Checklist de Etapas --}}
                                    <div class="px-4 py-2 space-y-1">
                                        @foreach($woa->stages as $stage)
                                            <label class="flex items-center space-x-3 py-1 px-2 rounded hover:bg-gray-50 cursor-pointer transition">
                                                <input type="checkbox" wire:click="toggleStage({{ $stage->id }})" {{ $stage->is_completed ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                                                <span class="{{ $stage->is_completed ? 'line-through text-gray-400' : 'text-gray-700' }} text-sm flex-1">
                                                    {{ $stage->areaStage->name }}
                                                </span>
                                                @if($stage->performer)
                                                    <span class="text-xs text-gray-400">{{ $stage->performer->name }}</span>
                                                @endif
                                                @if($stage->completed_at)
                                                    <span class="text-xs text-green-500">{{ $stage->completed_at->format('d/m H:i') }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-400">
                                    <p class="text-lg">Aún no se ha asignado a ningún área</p>
                                    <p class="text-sm mt-1">Usa el botón "Transferir a Área" para asignar.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Timeline de Trazabilidad --}}
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                        <div class="bg-gray-700 px-4 py-3">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Historial de Trazabilidad</h3>
                        </div>
                        <div class="p-4">
                            <div class="relative">
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                                @forelse($workOrder->traceabilityLogs as $log)
                                    <div class="relative pl-10 pb-4">
                                        <div class="absolute left-2.5 w-3 h-3 rounded-full {{ $log->result_state['css']['dot'] }} border-2 border-white shadow"></div>
                                        <div class="{{ $log->result_state['css']['bg'] }} border {{ $log->result_state['css']['border'] }} rounded-lg p-3 transition-colors duration-300">
                                            <div class="flex justify-between items-start mb-1">
                                                <div>
                                                    <span class="font-bold text-sm text-gray-800">{{ $log->action_label }}</span>
                                                    @if($log->toArea)
                                                        <span class="font-bold text-sm text-gray-700"> -> {{ $log->toArea->name }}</span>
                                                    @elseif($log->fromArea)
                                                        <span class="font-bold text-sm text-gray-700"> -> {{ $log->fromArea->name }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">{{ $log->created_at->format('d/m/Y - H:i') }}</span>
                                            </div>
                                            <div class="text-sm mt-1 text-gray-800 space-y-1">
                                                <div><span class="text-gray-600">Por:</span> <span class="font-bold">{{ $log->performer->name ?? 'Sistema' }}</span></div>
                                                <div class="flex items-center mt-1">
                                                    <span class="text-gray-600 mr-2">Estado:</span> 
                                                    <span class="font-bold {{ $log->result_state['css']['text'] }} px-2 py-0.5 {{ $log->result_state['css']['badge_bg'] }} rounded text-xs uppercase tracking-wider">
                                                        {{ $log->result_state['label'] }}
                                                    </span>
                                                </div>
                                                @if($log->notes && $log->action !== 'kanban_moved')
                                                    <div class="text-gray-500 italic mt-1.5 text-xs">Nota: {{ $log->notes }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-400 py-4">Sin registros de trazabilidad aún.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Transferir a Área --}}
    @if($showTransferModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" wire:click="closeTransferModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-6 pt-5 pb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 border-b pb-3">Transferir a Área</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Área Destino *</label>
                            <select wire:model="transferAreaId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Seleccionar área...</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                            @error('transferAreaId') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Supervisor/Doctor</label>
                            <select wire:model="transferSupervisorId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Sin supervisor</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Técnico Responsable</label>
                            <select wire:model="transferTechnicianId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Sin técnico</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Notas</label>
                            <textarea wire:model="transferNotes" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" placeholder="Observaciones de la transferencia..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3 border-t">
                    <button wire:click="closeTransferModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 text-sm">Cancelar</button>
                    <button wire:click="transferToArea" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Transferir</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
