<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Mi Perfil
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                    {{ $usuario->role?->nombre === 'admin' ? 'bg-purple-100/80 text-purple-800 border border-purple-200' : '' }}
                    {{ $usuario->role?->nombre === 'vendedor' ? 'bg-sky-100/80 text-sky-800 border border-sky-200' : '' }}
                    {{ $usuario->role?->nombre === 'cliente' ? 'bg-slate-200 text-slate-700' : '' }}">
                    {{ $usuario->role?->nombre ?? 'Sin rol' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        editandoPerfil: false, 
        modalNuevaDireccion: false, 
        modalEliminarDireccion: false, 
        formIdEliminar: null 
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ALERTA MENSAJES DE ESTADO -->
            @if(session('status'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold p-4 rounded-xl shadow-xs">
                    {{ session('status') }}
                </div>
            @endif

            <!-- BLOQUE 1: DATOS PERSONALES -->
            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Información Personal
                    </h3>
                    <button 
                        type="button" 
                        @click="editandoPerfil = !editandoPerfil" 
                        class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow-xs transition-colors cursor-pointer flex items-center gap-1.5"
                    >
                        <span x-text="editandoPerfil ? '✕ Cancelar Edición' : '✏️ Modificar Mis Datos'"></span>
                    </button>
                </div>

                <!-- VISTA MODO LECTURA -->
                <div x-show="!editandoPerfil" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-xs">
                        <div>
                            <span class="text-slate-400 font-bold uppercase block mb-1">Nombre Completo</span>
                            <p class="font-semibold text-slate-800 text-sm">{{ $usuario->nombre }} {{ $usuario->apellido }}</p>
                        </div>

                        <div>
                            <span class="text-slate-400 font-bold uppercase block mb-1">Correo Electrónico (No editable)</span>
                            <p class="font-semibold text-slate-800 text-sm">{{ $usuario->email }}</p>
                        </div>

                        <div>
                            <span class="text-slate-400 font-bold uppercase block mb-1">Documento</span>
                            <p class="font-semibold text-slate-800 text-sm">
                                @if($usuario->documento)
                                    <span class="bg-slate-200/70 text-slate-800 px-1.5 py-0.5 rounded text-[10px] font-bold mr-1">{{ $usuario->tipo_documento }}</span>
                                    {{ $usuario->documento }}
                                @else
                                    <span class="text-slate-400 italic">Sin registrar</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <span class="text-slate-400 font-bold uppercase block mb-1">Teléfono / Celular</span>
                            <p class="font-semibold text-slate-800 text-sm">{{ $usuario->telefono ?? 'Sin registrar' }}</p>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO MODO EDICIÓN DE PERFIL -->
                <div x-show="editandoPerfil" x-cloak class="pt-2">
                    <form method="POST" action="{{ route('perfil.update') }}" class="space-y-4 text-xs">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Nombre *" class="text-[11px] font-bold uppercase text-slate-600" />
                                <x-text-input type="text" name="nombre" :value="old('nombre', $usuario->nombre)" class="w-full mt-1 text-xs" required />
                                <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label value="Apellido *" class="text-[11px] font-bold uppercase text-slate-600" />
                                <x-text-input type="text" name="apellido" :value="old('apellido', $usuario->apellido)" class="w-full mt-1 text-xs" required />
                                <x-input-error :messages="$errors->get('apellido')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label value="Tipo de Documento" class="text-[11px] font-bold uppercase text-slate-600" />
                                <select name="tipo_documento" class="w-full mt-1 bg-slate-50 border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:border-indigo-500">
                                    <option value="CI" {{ old('tipo_documento', $usuario->tipo_documento) == 'CI' ? 'selected' : '' }}>C.I.</option>
                                    <option value="RUT" {{ old('tipo_documento', $usuario->tipo_documento) == 'RUT' ? 'selected' : '' }}>R.U.T.</option>
                                    <option value="DNI" {{ old('tipo_documento', $usuario->tipo_documento) == 'DNI' ? 'selected' : '' }}>D.N.I.</option>
                                    <option value="PASAPORTE" {{ old('tipo_documento', $usuario->tipo_documento) == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label value="Nº Documento" class="text-[11px] font-bold uppercase text-slate-600" />
                                <x-text-input type="text" name="documento" :value="old('documento', $usuario->documento)" class="w-full mt-1 text-xs" placeholder="Ej: 1.234.567-8" />
                            </div>

                            <div>
                                <x-input-label value="Teléfono / Celular" class="text-[11px] font-bold uppercase text-slate-600" />
                                <x-text-input type="text" name="telefono" :value="old('telefono', $usuario->telefono)" class="w-full mt-1 text-xs" placeholder="Ej: 099123456" />
                            </div>

                            <div>
                                <x-input-label value="Correo Electrónico (Solo Lectura)" class="text-[11px] font-bold uppercase text-slate-400" />
                                <x-text-input type="email" value="{{ $usuario->email }}" class="w-full mt-1 text-xs bg-slate-200 text-slate-500 cursor-not-allowed" disabled />
                            </div>
                        </div>

                        <!-- CAMBIO OPCIONAL DE CONTRASEÑA -->
                        <div class="border-t border-slate-200/80 pt-4 mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Nueva Contraseña (Opcional)" class="text-[11px] font-bold uppercase text-slate-600" />
                                <x-text-input type="password" name="password" class="w-full mt-1 text-xs" placeholder="Dejar en blanco para conservar la actual" />
                                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label value="Confirmar Nueva Contraseña" class="text-[11px] font-bold uppercase text-slate-600" />
                                <x-text-input type="password" name="password_confirmation" class="w-full mt-1 text-xs" placeholder="Repite la nueva contraseña" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t border-slate-200/80">
                            <button type="button" @click="editandoPerfil = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-xs">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-lg text-xs shadow-xs">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- BLOQUE 2: DIRECCIONES GUARDADAS EN TARJETAS -->
            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Mis Direcciones ({{ $usuario->direcciones->count() }})
                    </h3>
                    <button 
                        type="button" 
                        @click="modalNuevaDireccion = true" 
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-xs transition-colors flex items-center gap-1 cursor-pointer"
                    >
                        + Agregar Dirección
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

                            <!-- BOTÓN ELIMINAR DIRECCIÓN -->
                            <div class="pt-3 mt-2 border-t border-slate-100 flex justify-end">
                                <form action="{{ route('direcciones.destroy', $dir) }}" method="POST" id="form-delete-dir-{{ $dir->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="button" 
                                        @click="modalEliminarDireccion = true; formIdEliminar = 'form-delete-dir-{{ $dir->id }}'" 
                                        class="text-red-600 hover:text-red-800 text-[11px] font-bold bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-md transition-colors cursor-pointer"
                                    >
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-slate-400 text-xs font-medium">
                            No tienes direcciones de entrega registradas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- BLOQUE 3: HISTORIAL DE COMPRAS REALIZADAS -->
            <div class="bg-emerald-50/40 border border-emerald-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-emerald-200/80">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-xs">🛍️</span>
                        <h3 class="text-xs font-black uppercase tracking-wider text-emerald-900">
                            Mis Compras Realizadas ({{ $usuario->ventas->count() }})
                        </h3>
                    </div>
                </div>

                <div class="overflow-x-auto border border-emerald-200/60 rounded-xl bg-white shadow-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="px-4 py-3"># Compra</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Entrega</th>
                                <th class="px-4 py-3">Método Pago</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60 text-xs">
                            @forelse($usuario->ventas as $venta)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-900">#{{ $venta->id }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $venta->fecha_compra ? \Carbon\Carbon::parse($venta->fecha_compra)->format('d/m/Y H:i') : $venta->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 capitalize font-medium">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $venta->tipo_entrega === 'flete' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $venta->tipo_entrega }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 font-medium">{{ $venta->metodo_pago }}</td>
                                    <td class="px-4 py-3 text-right font-black text-indigo-700">$ {{ number_format($venta->precio_total, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            {{ $venta->estado === 'completada' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $venta->estado === 'pendiente' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $venta->estado === 'cancelada' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $venta->estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('ventas.show', $venta) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-[11px] bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md transition-colors">
                                            Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-slate-400 text-xs font-medium">
                                        Aún no has registrado ninguna compra en la tienda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BLOQUE 4: VENTAS REALIZADAS (SOLO PARA VENDEDORES O ADMINS) -->
            @if($usuario->role?->nombre === 'vendedor' || $usuario->role?->nombre === 'admin')
                <div class="bg-indigo-50/40 border border-indigo-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-indigo-200/80">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-indigo-100 text-indigo-700 rounded-lg text-xs">💼</span>
                            <h3 class="text-xs font-black uppercase tracking-wider text-indigo-900">
                                Mis Ventas Atendidas / Concretadas ({{ $usuario->ventasRealizadas->count() }})
                            </h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-indigo-200/60 rounded-xl bg-white shadow-xs">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    <th class="px-4 py-3"># Venta</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Cliente</th>
                                    <th class="px-4 py-3">Método Pago</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                    <th class="px-4 py-3 text-center">Estado</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/60 text-xs">
                                @forelse($usuario->ventasRealizadas as $venta)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-4 py-3 font-bold text-slate-900">#{{ $venta->id }}</td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ $venta->fecha_compra ? \Carbon\Carbon::parse($venta->fecha_compra)->format('d/m/Y H:i') : $venta->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-800 font-semibold">{{ $venta->cliente_nombre }}</td>
                                        <td class="px-4 py-3 text-slate-600 font-medium">{{ $venta->metodo_pago }}</td>
                                        <td class="px-4 py-3 text-right font-black text-indigo-700">$ {{ number_format($venta->precio_total, 2) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $venta->estado === 'completada' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ $venta->estado === 'pendiente' ? 'bg-amber-100 text-amber-800' : '' }}
                                                {{ $venta->estado === 'cancelada' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ $venta->estado }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('ventas.show', $venta) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-[11px] bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md transition-colors">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-slate-400 text-xs font-medium">
                                            Aún no has registrado ninguna venta realizada a clientes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        <!-- MODAL 1: AGREGAR NUEVA DIRECCIÓN -->
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
                        <button type="submit" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-lg text-xs shadow-xs">
                            Guardar Dirección
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: CONFIRMAR ELIMINACIÓN DE DIRECCIÓN -->
        <div 
            x-show="modalEliminarDireccion" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div @click.away="modalEliminarDireccion = false" class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center space-y-4">
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto text-xl">
                    🗑️
                </div>

                <div>
                    <h3 class="font-bold text-slate-800 text-base">¿Eliminar esta dirección?</h3>
                    <p class="text-xs text-slate-500 mt-1">Esta acción borrará la dirección del perfil de forma permanente.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button 
                        type="button" 
                        @click="modalEliminarDireccion = false" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        @click="document.getElementById(formIdEliminar).submit()" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition-colors cursor-pointer"
                    >
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>