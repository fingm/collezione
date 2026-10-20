<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Gasto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;

    // Modales
    public $mostrarModal = false;
    public $mostrarModalEliminar = false;
    public $gastoAEliminarId = null;
    public $mostrarModalConfirmarPago = false;
    public $gastoAPagarId = null;

    // Campos del Formulario (Gasto)
    public $gasto_id = null;
    public $concepto = '';
    public $monto = '';
    public $categoria = 'General';
    public $metodo_pago = 'Efectivo';
    public $estado_pago = 'pagado';
    public $fecha;
    public $fecha_vencimiento = null;
    public $comprobante_nro = '';
    public $observaciones = '';

    // Filtros
    public $filtroMes;
    public $filtroAnio;
    public $filtroEstadoPago = 'todos'; // todos, pagado, pendiente
    public $busqueda = '';

    public function mount()
    {
        $this->fecha = Carbon::today()->format('Y-m-d');
        $this->filtroMes = Carbon::now()->month;
        $this->filtroAnio = Carbon::now()->year;
    }

    // Modal de Confirmación de Pago
    public function solicitarConfirmacionPago($id)
    {
        $this->gastoAPagarId = $id;
        $this->mostrarModalConfirmarPago = true;
    }

    public function cerrarModalConfirmarPago()
    {
        $this->mostrarModalConfirmarPago = false;
        $this->gastoAPagarId = null;
    }

    public function marcarComoPagadoConfirmado()
    {
        if ($this->gastoAPagarId) {
            $gasto = Gasto::findOrFail($this->gastoAPagarId);
            $gasto->update([
                'estado_pago' => 'pagado'
            ]);

            session()->flash('status', 'El gasto fue registrado como PAGADO con éxito.');
        }

        $this->cerrarModalConfirmarPago();
    }

    public function abrirModalNuevo()
    {
        $this->limpiarFormulario();
        $this->fecha = Carbon::today()->format('Y-m-d');
        $this->mostrarModal = true;
    }

    public function seleccionarHoy()
    {
        $this->fecha = Carbon::today()->format('Y-m-d');
        $this->filtroMes = Carbon::now()->month;
        $this->filtroAnio = Carbon::now()->year;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->limpiarFormulario();
    }

    public function guardarGasto()
    {
        $this->validate([
            'concepto'          => 'required|string|max:255',
            'monto'             => 'required|numeric|min:0.01',
            'categoria'         => 'required|string',
            'metodo_pago'       => 'required|string',
            'estado_pago'       => 'required|in:pagado,pendiente',
            'fecha'             => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
        ], [
            'concepto.required' => 'El concepto del gasto es obligatorio.',
            'monto.required'    => 'Ingresa un monto válido.',
        ]);

        Gasto::updateOrCreate(
            ['id' => $this->gasto_id],
            [
                'concepto'          => $this->concepto,
                'monto'             => $this->monto,
                'categoria'         => $this->categoria,
                'metodo_pago'       => $this->metodo_pago,
                'estado_pago'       => $this->estado_pago,
                'fecha'             => $this->fecha,
                'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
                'comprobante_nro'   => $this->comprobante_nro,
                'observaciones'     => $this->observaciones,
                'usuario_id'        => Auth::id(),
            ]
        );

        session()->flash('status', $this->gasto_id ? 'Gasto actualizado con éxito.' : 'Gasto registrado correctamente.');

        $this->cerrarModal();
    }

    public function editarGasto($id)
    {
        $gasto = Gasto::findOrFail($id);
        $this->gasto_id          = $gasto->id;
        $this->concepto          = $gasto->concepto;
        $this->monto             = $gasto->monto;
        $this->categoria         = $gasto->categoria;
        $this->metodo_pago       = $gasto->metodo_pago;
        $this->estado_pago       = $gasto->estado_pago ?? 'pagado';
        $this->fecha             = $gasto->fecha ? $gasto->fecha->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $this->fecha_vencimiento = $gasto->fecha_vencimiento ? $gasto->fecha_vencimiento->format('Y-m-d') : null;
        $this->comprobante_nro   = $gasto->comprobante_nro;
        $this->observaciones     = $gasto->observaciones;

        $this->mostrarModal = true;
    }

    public function confirmarEliminarGasto($id)
    {
        $this->gastoAEliminarId = $id;
        $this->mostrarModalEliminar = true;
    }

    public function cerrarModalEliminar()
    {
        $this->mostrarModalEliminar = false;
        $this->gastoAEliminarId = null;
    }

    public function eliminarGasto()
    {
        if ($this->gastoAEliminarId) {
            Gasto::destroy($this->gastoAEliminarId);
            session()->flash('status', 'Gasto eliminado con éxito.');
        }

        $this->cerrarModalEliminar();
    }

    public function limpiarFormulario()
    {
        $this->reset(['gasto_id', 'concepto', 'monto', 'comprobante_nro', 'observaciones', 'fecha_vencimiento']);
        $this->categoria   = 'General';
        $this->metodo_pago = 'Efectivo';
        $this->estado_pago = 'pagado';
        $this->fecha       = Carbon::today()->format('Y-m-d');
        $this->resetValidation();
    }

    public function with(): array
    {
        $query = Gasto::whereYear('fecha', $this->filtroAnio)
            ->whereMonth('fecha', $this->filtroMes);

        if ($this->filtroEstadoPago !== 'todos') {
            $query->where('estado_pago', $this->filtroEstadoPago);
        }

        if (trim($this->busqueda) !== '') {
            $query->where(function ($q) {
                $q->where('concepto', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('categoria', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('comprobante_nro', 'like', '%' . $this->busqueda . '%');
            });
        }

        $gastos = $query->latest('fecha')->latest('id')->paginate(10);
        
        $totalGastosMes = Gasto::whereYear('fecha', $this->filtroAnio)
            ->whereMonth('fecha', $this->filtroMes)
            ->sum('monto');

        $totalPendienteCredito = Gasto::whereYear('fecha', $this->filtroAnio)
            ->whereMonth('fecha', $this->filtroMes)
            ->where('estado_pago', 'pendiente')
            ->sum('monto');

        return [
            'gastos'                => $gastos,
            'totalGastosMes'        => $totalGastosMes,
            'totalPendienteCredito' => $totalPendienteCredito,
        ];
    }
};
?>

<div class="space-y-6">

    <!-- ALERTA DE MENSAJES DE ÉXITO -->
    @if (session()->has('status'))
        <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold rounded-xl shadow-xs">
            {{ session('status') }}
        </div>
    @endif

    <!-- TARJETA SUPERIOR: TOTALES, BOTÓN CREAR Y FILTROS -->
    <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex flex-wrap justify-between items-center gap-4">
        
        <div class="flex gap-6">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Total Gastos del Mes</span>
                <span class="text-2xl font-black text-red-700">$ {{ number_format($totalGastosMes, 2) }}</span>
            </div>
            @if($totalPendienteCredito > 0)
                <div class="border-l border-slate-300 pl-6">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Pendientes de Pago (Crédito)</span>
                    <span class="text-2xl font-black text-amber-700">$ {{ number_format($totalPendienteCredito, 2) }}</span>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            
            <!-- Botón para Abrir Modal Nuevo Gasto -->
            <button 
                type="button" 
                wire:click="abrirModalNuevo" 
                class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs rounded-xl shadow-sm transition-all cursor-pointer flex items-center gap-1.5"
            >
                <span>+ Nuevo Gasto</span>
            </button>

            <!-- Filtros de Estado de Pago -->
            <select wire:model.live="filtroEstadoPago" class="bg-slate-50 border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:border-indigo-500">
                <option value="todos">Todos los Pagos</option>
                <option value="pagado">Pagados / Contado</option>
                <option value="pendiente">A Crédito / Pendientes</option>
            </select>

            <!-- Filtros por Mes y Año -->
            <div class="flex items-center gap-2">
                <select wire:model.live="filtroMes" class="bg-slate-50 border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:border-indigo-500">
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>

                <select wire:model.live="filtroAnio" class="bg-slate-50 border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:border-indigo-500">
                    @for($a = date('Y'); $a >= date('Y') - 3; $a--)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endfor
                </select>

                <!-- Botón Seleccionar Hoy -->
                <button 
                    type="button" 
                    wire:click="seleccionarHoy" 
                    class="px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                    title="Ir al mes actual"
                >
                    Hoy
                </button>
            </div>

        </div>
    </div>

    <!-- TABLA DE HISTORIAL DE GASTOS -->
    <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm space-y-3">
        <div class="flex justify-between items-center gap-3">
            <input 
                type="text" 
                wire:model.live.debounce.150ms="busqueda" 
                placeholder="Buscar por concepto, categoría o comprobante..." 
                class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 p-2.5 focus:border-indigo-500" 
            />
        </div>

        <div class="overflow-x-auto border border-slate-200/60 rounded-xl bg-white shadow-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3">Categoría / Pago</th>
                        <th class="px-4 py-3 text-center">Estado Pago</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-xs">
                    @forelse($gastos as $g)
                        <tr wire:key="gasto-row-{{ $g->id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-600 whitespace-nowrap">
                                {{ $g->fecha ? $g->fecha->format('d/m/Y') : '-' }}
                                @if($g->estado_pago === 'pendiente' && $g->fecha_vencimiento)
                                    <span class="text-[10px] block font-normal {{ $g->fecha_vencimiento->isPast() ? 'text-red-600 font-bold' : 'text-slate-400' }}">
                                        Vence: {{ $g->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-800">
                                {{ $g->concepto }}
                                @if($g->comprobante_nro)
                                    <span class="text-[10px] text-slate-400 block font-normal">Comp: {{ $g->comprobante_nro }}</span>
                                @endif
                                @if($g->observaciones)
                                    <span class="text-[10px] text-slate-500 block italic font-normal">{{ $g->observaciones }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-200 text-slate-700 font-bold block w-fit">
                                    {{ $g->categoria }}
                                </span>
                                <span class="text-[10px] text-slate-500 font-medium">{{ $g->metodo_pago }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(($g->estado_pago ?? 'pagado') === 'pagado')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Pagado
                                    </span>
                                @else
                                    <button 
                                        type="button" 
                                        wire:click="solicitarConfirmacionPago({{ $g->id }})" 
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 hover:bg-emerald-100 hover:text-emerald-800 transition-colors cursor-pointer"
                                        title="Hacer clic para solicitar confirmación de pago"
                                    >
                                        ⏳ Crédito (Pendiente)
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-black text-red-700 whitespace-nowrap">
                                $ {{ number_format($g->monto, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1.5">
                                    <button 
                                        type="button" 
                                        wire:click="editarGasto({{ $g->id }})" 
                                        class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-2.5 py-1 rounded-md text-[11px] font-bold transition-colors cursor-pointer"
                                    >
                                        Editar
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="confirmarEliminarGasto({{ $g->id }})" 
                                        class="bg-red-100 text-red-700 hover:bg-red-200 px-2.5 py-1 rounded-md text-[11px] font-bold transition-colors cursor-pointer"
                                    >
                                        Borrar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs font-medium">
                                No se encontraron gastos registrados en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $gastos->links() }}
        </div>
    </div>

    <!-- MODAL 1: REGISTRAR / EDITAR GASTO -->
    @if($mostrarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        {{ $gasto_id ? 'Editar Gasto' : 'Registrar Nuevo Gasto' }}
                    </h3>
                    <button type="button" wire:click="cerrarModal" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <div class="space-y-3.5 text-xs">
                    
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Concepto / Descripción *</label>
                        <input 
                            type="text" 
                            wire:model="concepto" 
                            class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500" 
                            placeholder="Ej: Compra de mercadería a crédito, Pago de Luz" 
                        />
                        @error('concepto') <span class="text-red-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Monto ($) *</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                wire:model="monto" 
                                class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500" 
                                placeholder="0.00" 
                            />
                            @error('monto') <span class="text-red-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-600">Fecha de Registro *</label>
                                <button type="button" wire:click="seleccionarHoy" class="text-[10px] font-bold text-indigo-600 hover:underline">
                                    Hoy
                                </button>
                            </div>
                            <input 
                                type="date" 
                                wire:model="fecha" 
                                class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500" 
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Estado de Pago *</label>
                            <select wire:model.live="estado_pago" class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:border-indigo-500">
                                <option value="pagado">Pagado (Contado)</option>
                                <option value="pendiente">Pendiente (A Crédito / Fiado)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Medio de Pago</label>
                            <select wire:model="metodo_pago" class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta / Débito</option>
                                <option value="Crédito Proveedor">Crédito Proveedor</option>
                            </select>
                        </div>
                    </div>

                    @if($estado_pago === 'pendiente')
                        <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                            <label class="text-xs font-bold uppercase tracking-wider text-amber-800 block mb-1">Fecha Limite de Vencimiento (Opcional)</label>
                            <input 
                                type="date" 
                                wire:model="fecha_vencimiento" 
                                class="w-full bg-white border-amber-300 rounded-lg text-xs text-slate-800 focus:border-amber-500" 
                            />
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Categoría</label>
                            <select wire:model="categoria" class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500">
                                <option value="General">General</option>
                                <option value="Mercadería / Insumos">Mercadería / Insumos</option>
                                <option value="Servicios">Servicios (Luz, Agua, Internet)</option>
                                <option value="Alquiler">Alquiler</option>
                                <option value="Mantenimiento">Mantenimiento / Reparaciones</option>
                                <option value="Sueldos">Sueldos / Adelantos</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Nº Comprobante / Factura</label>
                            <input 
                                type="text" 
                                wire:model="comprobante_nro" 
                                class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500" 
                                placeholder="Ej: Factura A-000123" 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-1">Observaciones</label>
                        <textarea 
                            wire:model="observaciones" 
                            rows="2" 
                            class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500 p-2" 
                            placeholder="Detalles adicionales, proveedor, cuotas..."
                        ></textarea>
                    </div>

                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button 
                        type="button" 
                        wire:click="cerrarModal" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="guardarGasto" 
                        class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        {{ $gasto_id ? 'Actualizar Gasto' : 'Guardar Gasto' }}
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- MODAL 2: CONFIRMAR ELIMINACIÓN -->
    @if($mostrarModalEliminar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto text-xl">
                    🗑️
                </div>

                <div>
                    <h3 class="font-bold text-slate-800 text-base">¿Eliminar este gasto?</h3>
                    <p class="text-xs text-slate-500 mt-1">Esta acción borrará el registro de gastos de forma permanente.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button 
                        type="button" 
                        wire:click="cerrarModalEliminar" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="eliminarGasto" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md transition-colors cursor-pointer"
                    >
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: CONFIRMAR PAGO -->
    @if($mostrarModalConfirmarPago)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-xl">
                    💳
                </div>

                <div>
                    <h3 class="font-bold text-slate-800 text-base">¿Confirmar Pago de Gasto?</h3>
                    <p class="text-xs text-slate-500 mt-1">El estado del gasto pasará de "Pendiente / Crédito" a "Pagado" y se actualizarán los balances de caja.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button 
                        type="button" 
                        wire:click="cerrarModalConfirmarPago" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="marcarComoPagadoConfirmado" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition-colors cursor-pointer"
                    >
                        Sí, marcar pagado
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>