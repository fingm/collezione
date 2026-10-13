<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('usuarios.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-200/70 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                    &larr; Volver
                </a>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    {{ $usuario->nombre_completo }}
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                    {{ $usuario->role?->nombre === 'admin' ? 'bg-purple-100/80 text-purple-800 border border-purple-200' : '' }}
                    {{ $usuario->role?->nombre === 'vendedor' ? 'bg-sky-100/80 text-sky-800 border border-sky-200' : '' }}
                    {{ $usuario->role?->nombre === 'cliente' ? 'bg-slate-200 text-slate-700' : '' }}">
                    {{ $usuario->role?->nombre ?? 'Sin rol' }}
                </span>
            </div>

            <a href="{{ route('usuarios.edit', $usuario) }}" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                ✏️ Editar Usuario
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- BLOQUE 1: DATOS PERSONALES -->
            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-200">
                    Información del Perfil
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-xs">
                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Documento</span>
                        <p class="font-semibold text-slate-800 text-sm">
                            @if($usuario->documento)
                                <span class="bg-slate-200/70 text-slate-800 px-1.5 py-0.5 rounded text-[10px] font-bold mr-1">{{ $usuario->tipo_documento }}</span>
                                {{ $usuario->documento }}
                            @else
                                <span class="text-slate-400">Sin registrar</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Correo Electrónico</span>
                        <p class="font-semibold text-slate-800 text-sm">{{ $usuario->email }}</p>
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Teléfono</span>
                        <p class="font-semibold text-slate-800 text-sm">{{ $usuario->telefono ?? 'Sin teléfono' }}</p>
                    </div>

                    <div>
                        <span class="text-slate-400 font-bold uppercase block mb-1">Estado de Cuenta</span>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold
                            {{ $usuario->activo ? 'bg-emerald-100/80 text-emerald-800 border border-emerald-200' : 'bg-red-100/80 text-red-800 border border-red-200' }}">
                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>

                @if($usuario->notas)
                    <div class="mt-4 pt-4 border-t border-slate-200/60">
                        <span class="text-slate-400 font-bold uppercase block mb-1 text-[11px]">Notas internas</span>
                        <p class="text-xs text-slate-700 bg-slate-200/50 p-3 rounded-lg border border-slate-200/80 italic">
                            "{{ $usuario->notas }}"
                        </p>
                    </div>
                @endif
            </div>

            <!-- BLOQUE 2: DIRECCIONES REGISTRADAS -->
            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Direcciones Guardadas ({{ $usuario->direcciones->count() }})
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($usuario->direcciones as $dir)
                        <div class="bg-white border border-slate-200/80 rounded-xl p-4 space-y-2 shadow-xs relative">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    📍 {{ $dir->alias ?: 'Dirección' }}
                                </span>
                                @if($dir->es_principal)
                                    <span class="bg-indigo-100 text-indigo-800 border border-indigo-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        Principal
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs font-medium text-slate-700">
                                {{ $dir->direccion_completa }}
                            </p>

                            <p class="text-[11px] text-slate-500">
                                {{ $dir->barrio ? $dir->barrio . ', ' : '' }}{{ $dir->ciudad }}, {{ $dir->departamento }}
                            </p>

                            @if($dir->indicaciones)
                                <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded border border-slate-100 mt-2">
                                    💡 {{ $dir->indicaciones }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-slate-400 text-xs">
                            Este usuario no tiene direcciones de entrega registradas.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>