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

    @php
        $dirPrincipal = $usuario->direccionPrincipal ?: $usuario->direcciones->first();
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                    <!-- SECCIÓN 2: DIRECCIÓN PRINCIPAL -->
                    <div class="pt-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 pb-2 border-b border-slate-200">
                            2. Dirección Principal
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="alias" value="Alias Dirección" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="alias" name="alias" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('alias', $dirPrincipal?->alias ?? 'Casa')" placeholder="Ej: Casa, Oficina, Depósito" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="direccion" value="Calle / Dirección" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('direccion', $dirPrincipal?->direccion)" placeholder="Ej: Av. Roosevelt" />
                            </div>

                            <div>
                                <x-input-label for="numero_puerta" value="Nº Puerta" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="numero_puerta" name="numero_puerta" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('numero_puerta', $dirPrincipal?->numero_puerta)" placeholder="1200" />
                            </div>

                            <div>
                                <x-input-label for="apartamento" value="Apto / Piso" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="apartamento" name="apartamento" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('apartamento', $dirPrincipal?->apartamento)" placeholder="402" />
                            </div>

                            <div>
                                <x-input-label for="esquina" value="Esquina" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="esquina" name="esquina" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('esquina', $dirPrincipal?->esquina)" placeholder="Parada 8" />
                            </div>

                            <div>
                                <x-input-label for="barrio" value="Barrio" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="barrio" name="barrio" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('barrio', $dirPrincipal?->barrio)" placeholder="Cantegril" />
                            </div>

                            <div>
                                <x-input-label for="ciudad" value="Ciudad" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="ciudad" name="ciudad" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('ciudad', $dirPrincipal?->ciudad ?? 'Maldonado')" />
                            </div>

                            <div>
                                <x-input-label for="departamento" value="Departamento" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="departamento" name="departamento" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('departamento', $dirPrincipal?->departamento ?? 'Maldonado')" />
                            </div>

                            <div class="md:col-span-3">
                                <x-input-label for="indicaciones" value="Indicaciones para el Envío" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <x-text-input id="indicaciones" name="indicaciones" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-slate-800 rounded-lg text-sm" :value="old('indicaciones', $dirPrincipal?->indicaciones)" placeholder="Ej: Portón negro, timbre no funciona, entregar de 14 a 18 hs" />
                            </div>
                        </div>
                    </div>

                    <!-- NOTAS Y ESTADO -->
                    <div class="pt-4 space-y-4">
                        <div>
                            <x-input-label for="notas" value="Notas de Cliente" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <textarea id="notas" name="notas" class="border-slate-300/80 bg-slate-50 focus:border-indigo-500 text-slate-800 rounded-lg shadow-sm w-full mt-1 text-sm" rows="2" placeholder="Observaciones o preferencias generales del cliente...">{{ old('notas', $usuario->notas) }}</textarea>
                        </div>

                        <div class="bg-slate-200/50 border border-slate-200/60 p-4 rounded-xl">
                            <label for="activo" class="inline-flex items-center cursor-pointer">
                                <input id="activo" type="checkbox" name="activo" value="1" class="rounded border-slate-300 text-indigo-700 shadow-sm focus:ring-indigo-500 w-4 h-4" {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                                <span class="ml-2.5 text-xs font-bold uppercase tracking-wider text-slate-700">Usuario Activo</span>
                            </label>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="border-t border-slate-200/80 pt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('usuarios.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300/80 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <x-primary-button class="bg-indigo-700 hover:bg-indigo-800 text-xs font-bold px-5 py-2.5 rounded-lg">
                            Guardar Cambios
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>