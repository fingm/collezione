<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Editar Usuario / Cliente: ') . $usuario->nombre_completo }}
            </h2>
            <a href="{{ route('usuarios.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-200/70 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                &larr; Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        modalEditarDireccion: false, 
        modalNuevaDireccion: false,
        dirEditar: {
            id: null,
            alias: '',
            direccion: '',
            numero_puerta: '',
            apartamento: '',
            esquina: '',
            barrio: '',
            ciudad: '',
            departamento: '',
            indicaciones: '',
            es_principal: false
        },
        cargarDireccion(dir) {
            this.dirEditar = { ...dir };
            this.modalEditarDireccion = true;
        }
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash de Estado -->
            @if(session('status'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold p-4 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                
                <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- SECCIÓN 1: DATOS PERSONALES -->
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-200">
                            1. Información del Usuario
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="nombre" value="Nombre *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('nombre', $usuario->nombre)" required />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="apellido" value="Apellido *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="apellido" name="apellido" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('apellido', $usuario->apellido)" required />
                                <x-input-error :messages="$errors->get('apellido')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tipo_documento" value="Tipo de Documento *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <select id="tipo_documento" name="tipo_documento" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm font-semibold">
                                    <option value="CI" {{ old('tipo_documento', $usuario->tipo_documento) == 'CI' ? 'selected' : '' }}>C.I.</option>
                                    <option value="RUT" {{ old('tipo_documento', $usuario->tipo_documento) == 'RUT' ? 'selected' : '' }}>R.U.T.</option>
                                    <option value="DNI" {{ old('tipo_documento', $usuario->tipo_documento) == 'DNI' ? 'selected' : '' }}>D.N.I.</option>
                                    <option value="PASAPORTE" {{ old('tipo_documento', $usuario->tipo_documento) == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                    <option value="OTRO" {{ old('tipo_documento', $usuario->tipo_documento) == 'OTRO' ? 'selected' : '' }}>Otro</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="documento" value="Documento" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="documento" name="documento" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('documento', $usuario->documento)" placeholder="Ej: 1.234.567-8" />
                                <x-input-error :messages="$errors->get('documento')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" value="Correo Electrónico *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('email', $usuario->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" value="Contraseña (Opcional)" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" placeholder="Dejar en blanco para conservar la actual" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="telefono" value="Teléfono / Celular" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('telefono', $usuario->telefono)" placeholder="Ej: 099123456" />
                            </div>

                            <div>
                                <x-input-label for="role_id" value="Rol en el Sistema *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <select id="role_id" name="role_id" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm font-semibold">
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" {{ old('role_id', $usuario->role_id) == $r->id ? 'selected' : '' }}>
                                            {{ ucfirst($r->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- NOTAS Y ESTADO -->
                    <div class="pt-4 space-y-4">
                        <div>
                            <x-input-label for="notas" value="Notas internas del Cliente" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <textarea id="notas" name="notas" class="border-slate-300/80 bg-slate-50 focus:border-indigo-500 text-slate-800 rounded-lg shadow-sm w-full mt-1 text-sm" rows="2" placeholder="Observaciones o preferencias generales del cliente...">{{ old('notas', $usuario->notas) }}</textarea>
                        </div>

                        <div class="bg-slate-200/50 border border-slate-200/60 p-4 rounded-xl">
                            <label for="activo" class="inline-flex items-center cursor-pointer">
                                <input id="activo" type="checkbox" name="activo" value="1" class="rounded border-slate-300 text-indigo-700 shadow-sm focus:ring-indigo-500 w-4 h-4" {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                                <span class="ml-2.5 text-xs font-bold uppercase tracking-wider text-slate-700">Usuario Activo</span>
                            </label>
                        </div>
                    </div>

                    <!-- BOTONES DE GUARDAR DATOS PERSONALES -->
                    <div class="border-t border-slate-200/80 pt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('usuarios.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300/80 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <x-primary-button class="bg-indigo-700 hover:bg-indigo-800 text-xs font-bold px-5 py-2.5 rounded-lg">
                            Actualizar Datos Personales
                        </x-primary-button>
                    </div>
                </form>

            </div>

            <!-- SECCIÓN 2: DIRECCIONES EN "CUADRITOS" / TARJETAS -->
            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        2. Direcciones Guardadas ({{ $usuario->direcciones->count() }})
                    </h3>
                    <button 
                        type="button"
                        @click="modalNuevaDireccion = true" 
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1 cursor-pointer"
                    >
                        + Nueva Dirección
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($usuario->direcciones as $dir)
                        <div class="bg-white border border-slate-200/80 rounded-xl p-4 space-y-2 shadow-xs relative flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
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

                            <!-- Botón para Editar Dirección con Modal -->
                            <div class="pt-3 mt-2 border-t border-slate-100 flex justify-end">
                                <button 
                                    type="button" 
                                    @click="cargarDireccion({{ $dir->toJson() }})"
                                    class="text-indigo-600 hover:text-indigo-900 text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md transition-colors cursor-pointer flex items-center gap-1"
                                >
                                    ✏️ Editar
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-slate-400 text-xs">
                            Este usuario no tiene direcciones de entrega registradas.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- MODAL EDITAR DIRECCIÓN -->
        <div 
            x-show="modalEditarDireccion" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div @click.away="modalEditarDireccion = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-3 border-slate-200">
                    <h3 class="font-bold text-slate-800 text-sm uppercase">Editar Dirección</h3>
                    <button @click="modalEditarDireccion = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form :action="'/direcciones/' + dirEditar.id" method="POST" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Alias (ej: Casa, Oficina)" class="text-[11px]" />
                            <x-text-input type="text" name="alias" x-model="dirEditar.alias" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label value="Calle / Dirección *" class="text-[11px]" />
                            <x-text-input type="text" name="direccion" x-model="dirEditar.direccion" class="w-full mt-1 text-xs" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <x-input-label value="N° Puerta" class="text-[11px]" />
                            <x-text-input type="text" name="numero_puerta" x-model="dirEditar.numero_puerta" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label value="Apto / Bis / Piso" class="text-[11px]" />
                            <x-text-input type="text" name="apartamento" x-model="dirEditar.apartamento" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label value="Esquina" class="text-[11px]" />
                            <x-text-input type="text" name="esquina" x-model="dirEditar.esquina" class="w-full mt-1 text-xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <x-input-label value="Barrio" class="text-[11px]" />
                            <x-text-input type="text" name="barrio" x-model="dirEditar.barrio" class="w-full mt-1 text-xs" />
                        </div>
                        <div>
                            <x-input-label value="Ciudad *" class="text-[11px]" />
                            <x-text-input type="text" name="ciudad" x-model="dirEditar.ciudad" class="w-full mt-1 text-xs" required />
                        </div>
                        <div>
                            <x-input-label value="Departamento *" class="text-[11px]" />
                            <x-text-input type="text" name="departamento" x-model="dirEditar.departamento" class="w-full mt-1 text-xs" required />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Indicaciones para el flete" class="text-[11px]" />
                        <x-text-input type="text" name="indicaciones" x-model="dirEditar.indicaciones" class="w-full mt-1 text-xs" />
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="es_principal" id="es_principal_edit" value="1" :checked="dirEditar.es_principal" class="rounded border-slate-300 text-indigo-600">
                        <label for="es_principal_edit" class="text-slate-700 font-semibold">Marcar como dirección principal</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalEditarDireccion = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-xs">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-lg text-xs shadow-sm">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL AGREGAR NUEVA DIRECCIÓN -->
        <div 
            x-show="modalNuevaDireccion" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div @click.away="modalNuevaDireccion = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-3 border-slate-200">
                    <h3 class="font-bold text-slate-800 text-sm uppercase">Agregar Nueva Dirección</h3>
                    <button @click="modalNuevaDireccion = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form action="{{ route('direcciones.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="usuario_id" value="{{ $usuario->id }}">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Alias (ej: Casa, Oficina)" class="text-[11px]" />
                            <x-text-input type="text" name="alias" class="w-full mt-1 text-xs" placeholder="Casa" />
                        </div>
                        <div>
                            <x-input-label value="Calle / Dirección *" class="text-[11px]" />
                            <x-text-input type="text" name="direccion" class="w-full mt-1 text-xs" required placeholder="Av. Roosevelt" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <x-input-label value="N° Puerta" class="text-[11px]" />
                            <x-text-input type="text" name="numero_puerta" class="w-full mt-1 text-xs" placeholder="1234" />
                        </div>
                        <div>
                            <x-input-label value="Apto / Bis / Piso" class="text-[11px]" />
                            <x-text-input type="text" name="apartamento" class="w-full mt-1 text-xs" placeholder="Apto 201" />
                        </div>
                        <div>
                            <x-input-label value="Esquina" class="text-[11px]" />
                            <x-text-input type="text" name="esquina" class="w-full mt-1 text-xs" placeholder="Pedragosa Sierra" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <x-input-label value="Barrio" class="text-[11px]" />
                            <x-text-input type="text" name="barrio" class="w-full mt-1 text-xs" placeholder="San Rafael" />
                        </div>
                        <div>
                            <x-input-label value="Ciudad *" class="text-[11px]" />
                            <x-text-input type="text" name="ciudad" value="Maldonado" class="w-full mt-1 text-xs" required />
                        </div>
                        <div>
                            <x-input-label value="Departamento *" class="text-[11px]" />
                            <x-text-input type="text" name="departamento" value="Maldonado" class="w-full mt-1 text-xs" required />
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Indicaciones para el flete" class="text-[11px]" />
                        <x-text-input type="text" name="indicaciones" class="w-full mt-1 text-xs" placeholder="Portón gris, timbre 2" />
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="es_principal" id="es_principal_new" value="1" class="rounded border-slate-300 text-indigo-600">
                        <label for="es_principal_new" class="text-slate-700 font-semibold">Marcar como dirección principal</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalNuevaDireccion = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-xs">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-lg text-xs shadow-sm">
                            Guardar Dirección
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>