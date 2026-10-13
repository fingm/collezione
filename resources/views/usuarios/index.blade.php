<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Gestión de Usuarios y Clientes') }}
            </h2>
            <a href="{{ route('usuarios.create') }}" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                + Nuevo usuario
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            
            @if (session('status'))
                <div class="bg-emerald-100/80 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <livewire:index-usuarios />       
        
        </div>
    </div>
</x-app-layout>