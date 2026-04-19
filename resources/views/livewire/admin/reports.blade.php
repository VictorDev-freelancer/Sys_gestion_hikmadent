<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generación de Reportes y Analíticas (ETL)') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-10 bg-white border-b border-gray-200">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Exportador de Datos de Laboratorio
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Filtra los registros operacionales del sistema para obtener un volcado de datos crudos (Formato CSV). Este extracto es ideal para procesarlo en Microsoft Excel o herramientas de Business Intelligence.
                        </p>
                    </div>

                    <form wire:submit="downloadReport" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Fechas --}}
                            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg border border-gray-100">
                                <div>
                                    <label for="startDate" class="block text-sm font-bold text-gray-700 mb-1">Fecha de Inicio *</label>
                                    <input type="date" wire:model="startDate" id="startDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                    @error('startDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="endDate" class="block text-sm font-bold text-gray-700 mb-1">Fecha de Fin *</label>
                                    <input type="date" wire:model="endDate" id="endDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                    @error('endDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Filtros Opcionales --}}
                            <div class="col-span-1 md:col-span-2">
                                <label for="areaId" class="block text-sm font-bold text-gray-700 mb-1">Filtrar por Área Operativa (Opcional)</label>
                                <select wire:model="areaId" id="areaId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white">
                                    <option value="">-- Todas las Áreas --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Si seleccionas un área, el reporte solo incluirá órdenes que hayan pasado por ella.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-6">
                            <a href="{{ route('dashboard') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition" wire:navigate>
                                Cancelar
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out transform hover:-translate-y-0.5">
                                <svg wire:loading.remove wire:target="downloadReport" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <svg wire:loading wire:target="downloadReport" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Descargar Dataset CSV
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            
            {{-- Instrucciones adicionales --}}
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Sobre la Lectura en Excel</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>El reporte es generado en formato universal separado por comas (CSV) con decodificación UTF-8 BOM. Al abrirlo en Excel, no deberías experimentar problemas con acentos o caracteres latinos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
