<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel Principal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12 text-center">
                <h1 class="text-4xl font-extrabold text-indigo-600 mb-4">
                    ¡Bienvenidos al Sistema Hikmadent!
                </h1>
                <p class="text-lg text-gray-600">
                    Por el momento se están preparando los módulos internos de gestión.<br>
                    Estás dentro de la plataforma principal.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
