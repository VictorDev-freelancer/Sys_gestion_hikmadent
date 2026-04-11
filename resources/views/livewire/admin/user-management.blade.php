<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Control y Gestión de Personal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <div></div>
                <button wire:click="create()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Añadir Nuevo Usuario
                </button>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                    <p class="font-bold">Notificación</p>
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Identidad</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Corporativo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento / Rol</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gestión</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full border border-gray-300" src="{{ rtrim(config('app.url'), '/') }}/{{ ltrim($user->profile_photo_url, '/') }}" onerror="this.onerror=null; this.src='{{ $user->profile_photo_url }}';" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach($user->roles as $role)
                                        @if($role->name === 'Super usuario')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                {{ $role->name }}
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                {{ $role->name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 mx-2 bg-indigo-50 border border-indigo-200 rounded px-3 py-1">Editar</button>
                                    <button wire:click="delete({{ $user->id }})" class="text-red-600 hover:text-red-900 bg-red-50 border border-red-200 rounded px-3 py-1" onclick="confirm('ATENCIÓN: ¿Estás seguro de que quieres eliminar a este empleado del sistema? Perderá el acceso de inmediato.') || event.stopImmediatePropagation()">Expulsar</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $users->links() }}
                </div>
                @endif
            </div>

            <!-- Jetstream Style Modal -->
            @if($isModalOpen)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="closeModal()"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200">
                        <form>
                            <div class="bg-white px-6 pt-5 pb-6 sm:p-8 sm:pb-6">
                                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-6 border-b pb-3">
                                    {{ $userId ? 'Actualizar Perfil de Empleado' : 'Registrar Nuevo Miembro' }}
                                </h3>
                                
                                <div class="mb-5">
                                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nombre completo:</label>
                                    <input type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" id="name" placeholder="Ej. Juan Pérez" wire:model="name">
                                    @error('name') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                </div>
                                
                                <div class="mb-5">
                                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Correo corporativo:</label>
                                    <input type="email" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" id="email" placeholder="ejemplo@hikmadent.com" wire:model="email">
                                    @error('email') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                </div>
                                
                                <div class="mb-5">
                                    <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Área / Rol designado:</label>
                                    <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full bg-white" id="role" wire:model="role_name">
                                        <option value="">Seleccione a qué área pertenecerá...</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_name') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                </div>
                                
                                <div class="mb-2 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                    <label for="password" class="block text-gray-800 text-sm font-bold mb-2">
                                        {{ $userId ? 'Cambiar Contraseña (Opcional)' : 'Contraseña de Acceso' }}
                                    </label>
                                    <input type="password" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full" id="password" wire:model="password" placeholder="{{ $userId ? 'Dejar en blanco para conservar la actual' : 'Crear contraseña temporal (mínimo 8)' }}">
                                    @error('password') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                                <button wire:click.prevent="store()" type="button" class="w-full inline-flex justify-center flex-shrink-0 rounded-md border border-transparent px-5 py-2 bg-indigo-600 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    {{ $userId ? 'Guardar Cambios' : 'Registrar Empleado' }}
                                </button>
                                <button wire:click="closeModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 px-5 py-2 bg-white text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
