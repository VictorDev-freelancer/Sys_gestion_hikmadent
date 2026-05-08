<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $itemId ? 'Editar Servicio' : 'Nuevo Servicio' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form wire:submit.prevent="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            {{-- Categoría --}}
                            <div class="col-span-1 md:col-span-2">
                                <label for="category" class="block text-sm font-medium text-gray-700">Categoría (ej: ZIRCONIO - CAD-CAM)</label>
                                <input type="text" wire:model="category" id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Nombre del Servicio --}}
                            <div class="col-span-1 md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Servicio</label>
                                <input type="text" wire:model="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Precio Regular --}}
                            <div>
                                <label for="price_regular" class="block text-sm font-medium text-gray-700">Precio Regular (S/)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm"> S/ </span>
                                    </div>
                                    <input type="number" step="0.01" wire:model="price_regular" id="price_regular" class="pl-8 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('price_regular') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Precio Estudiante --}}
                            <div>
                                <label for="price_student" class="block text-sm font-medium text-gray-700">Precio Estudiante (S/)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm"> S/ </span>
                                    </div>
                                    <input type="number" step="0.01" wire:model="price_student" id="price_student" class="pl-8 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                @error('price_student') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Activo --}}
                            <div class="col-span-1 md:col-span-2 flex items-center">
                                <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">Servicio Activo</label>
                            </div>

                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.catalog.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition" wire:navigate>Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Guardar Servicio</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
