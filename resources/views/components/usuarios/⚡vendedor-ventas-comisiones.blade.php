<?php

use App\Models\Usuario;
use App\Models\Venta;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Usuario $usuario;

    // Filtros por fecha para las ventas del vendedor
    public $fechaDesde = '';
    public $fechaHasta = '';

    // Comisión fija al 5%
    public $porcentajeComision = 5.0;

    public function mount(Usuario $usuario)
    {
        $this->usuario = $usuario;
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->endOfMonth()->toDateString();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $ventasVendedor = collect();
        $totalVendido = 0;
        $comisionTotal = 0;

        // Consulta si el usuario tiene el rol de Vendedor
        if ($this->usuario->role?->nombre === 'vendedor') {
            $baseQuery = Venta::where('vendedor_id', $this->usuario->id)
                ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha_compra', '>=', $this->fechaDesde))
                ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha_compra', '<=', $this->fechaHasta));

            $totalVendido = (float) (clone $baseQuery)->sum('precio_total');
            $comisionTotal = round($totalVendido * ($this->porcentajeComision / 100), 2);
            $ventasVendedor = $baseQuery->latest('fecha_compra')->paginate(10);
        }

        return [
            'ventasVendedor' => $ventasVendedor,
            'totalVendido'   => $totalVendido,
            'comisionTotal'  => $comisionTotal,
        ];
    }
};
?>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('usuarios.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-200/70 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                    &larr; Volver
                </a>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    {{ $usuario->nombre }} {{ $usuario->apellido }}
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

    <div class="py-12" x-data="{ modalNuevaDireccion: false, modalEliminarDireccion: false, formIdEliminar: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensaje de Estado / Flash Session -->
            @if(session('status'))
                <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold p-4 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

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
                    
                    <button 
                        @click="modalNuevaDireccion = true" 
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1 cursor-pointer"
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

                            <div class="pt-3 mt-2 border-t border-slate-100 flex justify-end">
                                <form action="{{ route('direcciones.destroy', $dir) }}" method="POST" id="form-delete-dir-{{ $dir->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="button" 
                                        @click="modalEliminarDireccion = true; formIdEliminar = 'form-delete-dir-{{ $dir->id }}'" 
                                        class="text-red-600 hover:text-red-800 text-[11px] font-bold bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-md transition-colors cursor-pointer flex items-center gap-1"
                                    >
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-6 text-slate-400 text-xs">
                            Este usuario no tiene direcciones de entrega registradas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- BLOQUE 3: HISTORIAL DE COMPRAS (SOLO PARA CLIENTES) -->
            @if($usuario->role?->nombre === 'cliente')
                <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Historial de Compras ({{ $usuario->ventas->count() }})
                        </h3>
                    </div>

                    <div class="overflow-x-auto border border-slate-200/60 rounded-xl bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    <th class="px-4 py-3"># Venta</th>
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
                                        <td class="px-4 py-3 font-bold text-slate-900">
                                            #{{ $venta->id }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ $venta->fecha_compra ? \Carbon\Carbon::parse($venta->fecha_compra)->format('d/m/Y H:i') : $venta->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 capitalize font-medium">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $venta->tipo_entrega === 'flete' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                                {{ $venta->tipo_entrega }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 font-medium">
                                            {{ $venta->metodo_pago }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-indigo-700">
                                            $ {{ number_format($venta->precio_total, 2) }}
                                        </td>
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
                                        <td colspan="7" class="px-4 py-6 text-center text-slate-400 text-xs">
                                            Este cliente aún no ha registrado ninguna compra.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- BLOQUE 4: VENTAS Y COMISIONES (SOLO PARA VENDEDORES) -->
            @if($usuario->role?->nombre === 'vendedor')
                <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Ventas Realizadas por el Vendedor
                        </h3>

                        <!-- Filtro por Rango de Fechas -->
                        <div class="flex items-center gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Desde</span>
                                <input 
                                    type="date" 
                                    wire:model.live="fechaDesde" 
                                    class="py-1 px-2 text-xs bg-white border-slate-300 rounded-lg text-slate-800 font-medium focus:border-indigo-500 focus:ring-0"
                                >
                            </div>
                            <span class="text-slate-400 text-xs mt-3">-</span>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Hasta</span>
                                <input 
                                    type="date" 
                                    wire:model.live="fechaHasta" 
                                    class="py-1 px-2 text-xs bg-white border-slate-300 rounded-lg text-slate-800 font-medium focus:border-indigo-500 focus:ring-0"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Ventas y Comisión -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-md">
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Total Vendido</span>
                            <span class="text-base font-black text-slate-900">$ {{ number_format($totalVendido, 2) }}</span>
                        </div>
                        <div class="bg-emerald-50/80 p-3.5 rounded-xl border border-emerald-200 shadow-xs">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Comisión ({{ $porcentajeComision }}%)</span>
                            <span class="text-base font-black text-emerald-700">$ {{ number_format($comisionTotal, 2) }}</span>
                        </div>
                    </div>

                    <!-- Tabla de Importe y Comisión por Operación -->
                    <div class="overflow-x-auto border border-slate-200/60 rounded-xl bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    <th class="px-4 py-3"># Venta</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Cliente</th>
                                    <th class="px-4 py-3 text-right">Importe Venta</th>
                                    <th class="px-4 py-3 text-right">Comisión ({{ $porcentajeComision }}%)</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/60 text-xs">
                                @forelse($ventasVendedor as $v)
                                    @php
                                        $comisionUnit = round($v->precio_total * ($porcentajeComision / 100), 2);
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-4 py-3 font-bold text-slate-900">#{{ $v->id }}</td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ \Carbon\Carbon::parse($v->fecha_compra)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-800">
                                            {{ $v->cliente_nombre }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-slate-900">
                                            $ {{ number_format($v->precio_total, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                            $ {{ number_format($comisionUnit, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('ventas.show', $v) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-[11px] bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md transition-colors">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-slate-400 text-xs">
                                            No hay ventas registradas para este vendedor en el rango seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación de las Ventas -->
                    @if($ventasVendedor && $ventasVendedor->hasPages())
                        <div class="pt-2">
                            {{ $ventasVendedor->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>

        <!-- MODAL AGREGAR DIRECCIÓN (Alpine.js) -->
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
                        <input type="checkbox" name="es_principal" id="es_principal" value="1" class="rounded border-slate-300 text-indigo-600">
                        <label for="es_principal" class="text-slate-700 font-semibold">Marcar como dirección principal</label>
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

        <!-- MODAL CONFIRMAR ELIMINACIÓN DE DIRECCIÓN (Alpine.js) -->
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
                    <p class="text-xs text-slate-500 mt-1">Esta acción borrará la dirección del registro del usuario de forma permanente.</p>
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