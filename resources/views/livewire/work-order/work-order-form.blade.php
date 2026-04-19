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
                        <div>
                            <label for="prosthetic_type" class="block text-sm font-bold text-gray-700 mb-1">Tipo Protésico *</label>
                            <select wire:model="prosthetic_type" id="prosthetic_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Seleccionar tipo...</option>
                                @foreach($prostheticTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('prosthetic_type') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-bold text-gray-700 mb-1">Cantidad *</label>
                            <input type="number" wire:model="quantity" id="quantity" min="1" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                            @error('quantity') <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="color" class="block text-sm font-bold text-gray-700 mb-1">Color</label>
                            <input type="text" wire:model="color" id="color" placeholder="Ej: A2, B1, etc." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div>
                            <label for="final_work_type" class="block text-sm font-bold text-gray-700 mb-1">Tipo de Trabajo Final</label>
                            <input type="text" wire:model="final_work_type" id="final_work_type" placeholder="Descripción del trabajo final" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                        </div>
                        <div class="md:col-span-2">
                            <label for="specifications" class="block text-sm font-bold text-gray-700 mb-1">Especificaciones</label>
                            <textarea wire:model="specifications" id="specifications" rows="3" placeholder="Detalles adicionales del trabajo..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"></textarea>
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

                {{-- SECCIÓN 5: Asignaciones --}}
                <div class="bg-white shadow-xl rounded-lg mb-6 overflow-hidden">
                    <div class="bg-pink-600 px-6 py-3">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Asignación
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="assigned_tpd_id" class="block text-sm font-bold text-gray-700 mb-1">TPD Responsable</label>
                            <select wire:model="assigned_tpd_id" id="assigned_tpd_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Seleccionar TPD...</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="initial_area_id" class="block text-sm font-bold text-gray-700 mb-1">Área Inicial (opcional)</label>
                            <select wire:model="initial_area_id" id="initial_area_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Sin asignar aún</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($initial_area_id)
                        <div>
                            <label for="area_supervisor_id" class="block text-sm font-bold text-gray-700 mb-1">Doctor/Supervisor del Área</label>
                            <select wire:model="area_supervisor_id" id="area_supervisor_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Sin supervisor</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="area_technician_id" class="block text-sm font-bold text-gray-700 mb-1">Técnico del Área</label>
                            <select wire:model="area_technician_id" id="area_technician_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                <option value="">Sin técnico asignado</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
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
