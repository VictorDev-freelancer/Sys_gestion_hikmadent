<div>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="h-6 w-6 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ __('Control y Gestión de Personal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Tabs Modernos y Premium --}}
            <div class="mb-8 bg-white p-2 rounded-xl shadow-md border border-gray-100 flex space-x-2">
                <button wire:click="switchTab('personal')" class="flex-1 py-3 px-4 rounded-lg font-bold text-sm flex items-center justify-center transition-all duration-200 {{ $activeTab === 'personal' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                    <svg class="h-5 w-5 mr-2 {{ $activeTab === 'personal' ? 'text-white' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Control de Personal (Colaboradores)
                </button>

                <button wire:click="switchTab('logins')" class="flex-1 py-3 px-4 rounded-lg font-bold text-sm flex items-center justify-center transition-all duration-200 {{ $activeTab === 'logins' ? 'bg-violet-600 text-white shadow-md' : 'text-gray-600 hover:text-violet-600 hover:bg-violet-50/50' }}">
                    <svg class="h-5 w-5 mr-2 {{ $activeTab === 'logins' ? 'text-white' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Logins de Acceso (Administración)
                </button>
            </div>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">
                        @if($activeTab === 'personal')
                            Lista de Técnicos y Personal Operativo
                        @else
                            Lista de Accesos Administrativos
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($activeTab === 'personal')
                            Registra y gestiona los colaboradores de las áreas de trabajo para asignación de tareas.
                        @else
                            Administra las cuentas autorizadas para iniciar sesión y gestionar el sistema.
                        @endif
                    </p>
                </div>
                
                @if($activeTab === 'personal')
                    <button wire:click="create()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Registrar Personal
                    </button>
                @else
                    <button wire:click="create()" class="bg-violet-600 hover:bg-violet-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg flex items-center transition duration-150 ease-in-out">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Crear Acceso Admin
                    </button>
                @endif
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center justify-between animate-fade-in" role="alert">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-bold text-sm">Operación Exitosa</p>
                            <p class="text-xs">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    @if($activeTab === 'personal') Colaborador @else Administrador @endif
                                </th>
                                @if($activeTab === 'logins')
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Correo de Acceso</th>
                                @endif
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Área / Rol Designado</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full border-2 border-gray-100 object-cover shadow-sm" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-[10px] font-mono text-gray-400">ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                @if($activeTab === 'logins')
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600 font-mono">{{ $user->email }}</div>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach($user->roles as $role)
                                        @if($role->name === 'Super usuario')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                                👑 {{ $role->name }}
                                            </span>
                                        @elseif($role->name === 'Administración')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-violet-100 text-violet-800 border border-violet-200 shadow-sm">
                                                💼 {{ $role->name }}
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm">
                                                🛠️ {{ $role->name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg px-3 py-1.5 transition">
                                            Editar
                                        </button>
                                        <button wire:click="delete({{ $user->id }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg px-3 py-1.5 transition" onclick="confirm('¿Estás seguro de que quieres remover este registro permanentemente del sistema?') || event.stopImmediatePropagation()">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="text-base font-bold">No se encontraron registros</p>
                                    <p class="text-xs text-gray-400 mt-1">Registra nuevos miembros para visualizarlos en este listado.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 sm:px-6">
                    {{ $users->links() }}
                </div>
                @endif
            </div>

            {{-- MODAL CRUD (Jetstream Style) --}}
            @if($isModalOpen)
            <div class="fixed inset-0 z-50 overflow-y-auto animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    
                    <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="closeModal()"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                        <form wire:submit.prevent="store">
                            <div class="bg-white px-6 pt-5 pb-6 sm:p-8 sm:pb-6">
                                <h3 class="text-lg leading-6 font-bold text-gray-900 mb-6 border-b pb-3 flex items-center">
                                    @if($activeTab === 'personal')
                                        <svg class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $userId ? 'Actualizar Ficha de Colaborador' : 'Registrar Nuevo Colaborador' }}
                                    @else
                                        <svg class="h-5 w-5 mr-2 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        {{ $userId ? 'Actualizar Acceso Administrativo' : 'Crear Acceso Administrativo' }}
                                    @endif
                                </h3>
                                
                                <div class="space-y-4">
                                    {{-- Nombre completo --}}
                                    <div>
                                        <label for="name" class="block text-gray-700 text-sm font-bold mb-1.5">Nombre completo:</label>
                                        <input type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" id="name" placeholder="Ej. Juan Pérez" wire:model="name">
                                        @error('name') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                    </div>
                                    
                                    {{-- Área / Rol designado --}}
                                    <div>
                                        <label for="role" class="block text-gray-700 text-sm font-bold mb-1.5">
                                            @if($activeTab === 'personal') Área de Trabajo: @else Nivel de Acceso: @endif
                                        </label>
                                        <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full bg-white text-sm" id="role" wire:model="role_name">
                                            <option value="">Seleccionar opción...</option>
                                            @foreach($modalRoles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('role_name') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                    </div>
                                    
                                    {{-- CAMPOS EXCLUSIVOS PARA LOGINS DE ACCESO (ADMINISTRATIVOS) --}}
                                    @if($activeTab === 'logins')
                                        {{-- Correo corporativo --}}
                                        <div>
                                            <label for="email" class="block text-gray-700 text-sm font-bold mb-1.5">Correo Corporativo / Usuario:</label>
                                            <input type="email" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm font-mono" id="email" placeholder="ejemplo@hikmadent.com" wire:model="email">
                                            @error('email') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                        </div>
                                        
                                        {{-- Contraseña --}}
                                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-150">
                                            <label for="password" class="block text-gray-800 text-sm font-bold mb-1.5">
                                                {{ $userId ? 'Cambiar Contraseña (Opcional)' : 'Contraseña de Acceso:' }}
                                            </label>
                                            <input type="password" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full text-sm" id="password" wire:model="password" placeholder="{{ $userId ? 'Dejar vacío para conservar actual' : 'Mínimo 8 caracteres' }}">
                                            @error('password') <span class="text-red-600 text-xs font-semibold mt-1 block">{{ $message }}</span>@enderror
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
                                <button wire:click="closeModal()" type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium transition shadow-sm">
                                    Cancelar
                                </button>
                                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-medium text-white shadow-md transition {{ $activeTab === 'personal' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-violet-600 hover:bg-violet-700' }}">
                                    {{ $userId ? 'Guardar Cambios' : 'Registrar' }}
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
