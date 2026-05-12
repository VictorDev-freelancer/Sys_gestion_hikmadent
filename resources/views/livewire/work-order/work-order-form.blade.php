<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $workOrderId ? 'Editar Orden de Trabajo' : 'Nueva Orden de Trabajo' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            <form wire:submit="save">
                {{-- SECCIÓN 1: Datos del Cliente/Doctor --}}
                <div class="bg-white shadow-xl rounded-lg mb-6 overflow-hidden">
                    <div class="bg-indigo-600 px-6 py-3">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Dr.(a) — Consultorio
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="doctor_name" class="block text-sm font-bold text-gray-700 mb-1">Dr.(a) *</label>
                            <input type="text" wire:model="doctor_name" id="doctor_name" placeholder="Nombre del doctor" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            @error('doctor_name') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="clinic_name" class="block text-sm font-bold text-gray-700 mb-1">Consultorio</label>
                            <input type="text" wire:model="clinic_name" id="clinic_name" placeholder="Nombre del consultorio" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="client_phone" class="block text-sm font-bold text-gray-700 mb-1">Teléfono</label>
                            <input type="text" wire:model="client_phone" id="client_phone" placeholder="Teléfono de contacto" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="client_email" class="block text-sm font-bold text-gray-700 mb-1">Correo</label>
                            <input type="email" wire:model="client_email" id="client_email" placeholder="correo@ejemplo.com" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: Datos del Paciente --}}
                <div class="bg-white shadow-xl rounded-lg mb-6 overflow-hidden">
                    <div class="bg-emerald-600 px-6 py-3">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Datos del Paciente
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="md:col-span-2">
                            <label for="patient_name" class="block text-sm font-bold text-gray-700 mb-1">Paciente *</label>
                            <input type="text" wire:model="patient_name" id="patient_name" placeholder="Nombre completo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            @error('patient_name') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tipo de Cliente *</label>
                            <select wire:model.live="client_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="regular">Persona Natural / Clínica</option>
                                <option value="student">Estudiante</option>
                            </select>
                            @error('client_type') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="patient_age" class="block text-sm font-bold text-gray-700 mb-1">Edad</label>
                            <input type="number" wire:model="patient_age" id="patient_age" min="1" max="120" placeholder="Años" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: Tipo de Trabajo Protésico --}}
                <div class="bg-white shadow-xl rounded-lg mb-6 overflow-hidden">
                    <div class="bg-violet-600 px-6 py-3">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Tipo de Trabajo Protésico
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="catalog_item_id" class="block text-sm font-bold text-gray-700 mb-1">Servicio del Catálogo *</label>
                            <select wire:model.live="catalog_item_id" id="catalog_item_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Seleccionar trabajo...</option>
                                @foreach($catalogItems as $category => $items)
                                    <optgroup label="{{ $category }}">
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }} (S/ {{ number_format($client_type === 'student' ? $item->price_student : $item->price_regular, 2) }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('catalog_item_id') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-bold text-gray-700 mb-1">Cantidad *</label>
                            <input type="number" wire:model.live="quantity" id="quantity" min="1" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            @error('quantity') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="flex items-end">
                            <div class="w-full bg-gray-50 rounded p-3 border border-gray-200">
                                <div class="flex justify-between items-center text-sm mb-1">
                                    <span class="text-gray-500">Precio Unitario:</span>
                                    <span class="font-semibold text-gray-700">S/ {{ number_format($unit_price, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-base border-t border-gray-200 pt-1 mt-1">
                                    <span class="text-gray-700 font-bold">Total a Facturar:</span>
                                    <span class="font-bold text-indigo-700">S/ {{ number_format($total_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="color" class="block text-sm font-bold text-gray-700 mb-1">Color</label>
                            <input type="text" wire:model="color" id="color" placeholder="Ej: A2, B1, etc." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>

                        {{-- Trabajos Extras Dinámicos --}}
                        <div class="md:col-span-2 mt-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-bold text-gray-700">Trabajos Extras</h4>
                                <button type="button" wire:click="addExtraWork" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition text-sm font-semibold">
                                    + Añadir Extra
                                </button>
                            </div>

                            @foreach($extra_works as $index => $extra)
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-grow">
                                        <select wire:model.live="extra_works.{{ $index }}.catalog_item_id" wire:change="updateExtraWorkDetails({{ $index }})" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm bg-white">
                                            <option value="">Seleccionar extra del catálogo...</option>
                                            @foreach($catalogItems as $category => $items)
                                                <optgroup label="{{ $category }}">
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error("extra_works.{$index}.catalog_item_id") <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="w-32 relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">S/</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="extra_works.{{ $index }}.price" class="pl-8 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm bg-gray-100" readonly placeholder="0.00">
                                    </div>
                                    <button type="button" wire:click="removeExtraWork({{ $index }})" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            @endforeach
                            @if(count($extra_works) === 0)
                                <p class="text-sm text-gray-500 italic">No hay trabajos extras agregados a esta orden.</p>
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <label for="specifications" class="block text-sm font-bold text-gray-700 mb-1">Especificaciones Adicionales</label>
                            <textarea wire:model="specifications" id="specifications" rows="3" placeholder="Detalles adicionales del trabajo principal..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"></textarea>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 4: Fechas y Prioridad --}}
                <div class="bg-white shadow-xl rounded-lg mb-6 overflow-hidden">
                    <div class="bg-amber-600 px-6 py-3">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Fechas y Prioridad
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label for="order_date" class="block text-sm font-bold text-gray-700 mb-1">Fecha de Orden</label>
                            <input type="date" wire:model="order_date" id="order_date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="technical_send_date" class="block text-sm font-bold text-gray-700 mb-1">Fecha Envío Técnico</label>
                            <input type="date" wire:model="technical_send_date" id="technical_send_date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="clinic_delivery_date" class="block text-sm font-bold text-gray-700 mb-1">Entrega en Clínica</label>
                            <input type="date" wire:model="clinic_delivery_date" id="clinic_delivery_date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="delivery_date" class="block text-sm font-bold text-gray-700 mb-1">Fecha de Entrega Final</label>
                            <input type="date" wire:model="delivery_date" id="delivery_date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-bold text-gray-700 mb-1">Prioridad *</label>
                            <select wire:model="priority" id="priority" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 5: Asignación y Workflow --}}
                <div class="bg-white shadow-xl rounded-lg mb-6 overflow-hidden">
                    <div class="bg-pink-600 px-6 py-3">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Asignación Global y Flujo de Trabajo (Ruta)
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-6 max-w-md">
                            <label for="assigned_tpd_id" class="block text-sm font-bold text-gray-700 mb-1">TPD Responsable (Global)</label>
                            <select wire:model="assigned_tpd_id" id="assigned_tpd_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Seleccionar TPD Responsable del Proyecto...</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="mb-6">

                        <h4 class="text-md font-bold text-gray-800 mb-4">Ruta Planificada de Áreas</h4>
                        <p class="text-sm text-gray-500 mb-4">Define el orden en que las áreas procesarán este trabajo. Al completar un paso, pasará automáticamente al siguiente.</p>

                        <div class="space-y-4">
                            @foreach($planned_route as $index => $step)
                                <div class="flex items-start space-x-4 bg-gray-50 p-4 rounded-lg border border-gray-200" wire:key="route-step-{{ $index }}">
                                    <div class="pt-2">
                                        <span class="bg-gray-800 text-white font-bold rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Área</label>
                                            <select wire:model="planned_route.{{ $index }}.area_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white text-sm">
                                                <option value="">Seleccionar área...</option>
                                                @foreach($areas as $area)
                                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Técnico Asignado (opcional)</label>
                                            <select wire:model="planned_route.{{ $index }}.technician_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white text-sm">
                                                <option value="">Cualquier técnico del área</option>
                                                @foreach($technicians as $tech)
                                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="pt-6">
                                        <button type="button" wire:click="removeRouteStep({{ $index }})" class="text-red-500 hover:text-red-700" title="Eliminar paso">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="button" wire:click="addRouteStep" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-pink-600 hover:bg-pink-700 focus:outline-none">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Añadir siguiente paso
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('work-orders.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium shadow-sm" wire:navigate>
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white font-bold shadow-lg transition duration-150">
                        {{ $workOrderId ? 'Guardar Cambios' : 'Crear Orden de Trabajo' }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
