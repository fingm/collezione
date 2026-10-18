<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Envio;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    // Filtros de la tabla principal
    public $filtroFecha;
    public $filtroEstado = 'todos';
    public $busqueda = '';

    // Estado para el Modal de Entrega
    public $mostrarModalEntrega = false;
    public $envioSeleccionadoId = null;
    public $fechaEntregaModal;
    public $cadeteModal = '';
    public $notasEntregaModal = '';

    // Estado para el Modal de Ver/Imprimir Orden de Envío
    public $mostrarModalVerEnvio = false;
    public $envioDetalle = null;

    public function mount()
    {
        $this->filtroFecha = Carbon::today()->format('Y-m-d');
        $this->fechaEntregaModal = Carbon::now()->format('Y-m-d\TH:i');
    }

    // Abrir Modal de Ver/Imprimir Orden de Envío
    public function verEnvio($envioId)
    {
        $this->envioDetalle = Envio::with(['venta.detalles.producto', 'venta.cliente', 'direccion'])->find($envioId);
        if ($this->envioDetalle) {
            $this->mostrarModalVerEnvio = true;
        }
    }

    public function cerrarModalVerEnvio()
    {
        $this->mostrarModalVerEnvio = false;
        $this->envioDetalle = null;
    }

    // Abrir Modal para confirmar entrega
    public function abrirModalEntrega($envioId)
    {
        $envio = Envio::find($envioId);
        if (!$envio) return;

        $this->envioSeleccionadoId = $envio->id;
        $this->cadeteModal = $envio->cadete_o_transporte ?? '';
        $this->notasEntregaModal = $envio->notas_envio ?? '';
        $this->fechaEntregaModal = Carbon::now()->format('Y-m-d\TH:i');
        $this->mostrarModalEntrega = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModalEntrega = false;
        $this->envioSeleccionadoId = null;
    }

    public function confirmarEntrega()
    {
        if (!$this->envioSeleccionadoId) return;

        $envio = Envio::findOrFail($this->envioSeleccionadoId);
        $envio->update([
            'estado'               => 'entregado',
            'fecha_entrega'        => $this->fechaEntregaModal ? Carbon::parse($this->fechaEntregaModal) : now(),
            'cadete_o_transporte'  => $this->cadeteModal,
            'notas_envio'          => $this->notasEntregaModal,
        ]);

        $this->cerrarModal();
        session()->flash('status', 'El envío #' . $envio->id . ' fue marcado como entregado con éxito.');
    }

    public function marcarEnCamino($envioId)
    {
        $envio = Envio::findOrFail($envioId);
        $envio->update(['estado' => 'en_camino']);
    }

    public function with(): array
    {
        $queryDelDia = Envio::with(['venta.cliente', 'direccion'])
            ->when($this->filtroFecha, function ($q) {
                $q->whereDate('fecha_pactada', $this->filtroFecha);
            })
            ->when($this->filtroEstado !== 'todos', function ($q) {
                $q->where('estado', $this->filtroEstado);
            })
            ->when($this->busqueda !== '', function ($q) {
                $q->whereHas('direccion', function ($sub) {
                    $sub->where('direccion', 'like', '%' . $this->busqueda . '%')
                        ->orWhere('barrio', 'like', '%' . $this->busqueda . '%');
                })->orWhereHas('venta', function ($sub) {
                    $sub->where('cliente_nombre', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->orderByRaw("CASE 
                WHEN estado = 'pendiente' THEN 1 
                WHEN estado = 'en_camino' THEN 2 
                WHEN estado = 'entregado' THEN 3 
                ELSE 4 END")
            ->latest('id');

        $enviosDelDia = $queryDelDia->paginate(10, ['*'], 'page_dia');

        $enviosPendientesOtrosDias = Envio::with(['venta.cliente', 'direccion'])
            ->whereIn('estado', ['pendiente', 'en_camino'])
            ->when($this->filtroFecha, function ($q) {
                $q->whereDate('fecha_pactada', '!=', $this->filtroFecha);
            })
            ->orderBy('fecha_pactada', 'asc')
            ->get();

        $hoy = Carbon::today()->format('Y-m-d');
        $kpiPendientesHoy = Envio::whereDate('fecha_pactada', $hoy)->where('estado', 'pendiente')->count();
        $kpiEnCaminoHoy   = Envio::whereDate('fecha_pactada', $hoy)->where('estado', 'en_camino')->count();
        $kpiEntregadosHoy = Envio::whereDate('fecha_pactada', $hoy)->where('estado', 'entregado')->count();
        $kpiPendientesAtrasados = Envio::whereDate('fecha_pactada', '<', $hoy)->whereIn('estado', ['pendiente', 'en_camino'])->count();

        return [
            'enviosDelDia'              => $enviosDelDia,
            'enviosPendientesOtrosDias' => $enviosPendientesOtrosDias,
            'kpiPendientesHoy'          => $kpiPendientesHoy,
            'kpiEnCaminoHoy'            => $kpiEnCaminoHoy,
            'kpiEntregadosHoy'          => $kpiEntregadosHoy,
            'kpiPendientesAtrasados'    => $kpiPendientesAtrasados,
        ];
    }
};
?>

<div class="space-y-6">

    <!-- MENSAJE DE ESTADO -->
    @if (session()->has('status'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl text-emerald-800 text-xs font-bold shadow-xs">
            {{ session('status') }}
        </div>
    @endif

    <!-- TARJETAS DE RESUMEN (KPIs) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Pendientes Hoy</span>
                <span class="text-xl font-black text-amber-600">{{ $kpiPendientesHoy }}</span>
            </div>
            <div class="p-2 bg-amber-100 text-amber-700 rounded-lg text-lg">⏳</div>
        </div>

        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">En Camino Hoy</span>
                <span class="text-xl font-black text-indigo-600">{{ $kpiEnCaminoHoy }}</span>
            </div>
            <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg text-lg">🚚</div>
        </div>

        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Entregados Hoy</span>
                <span class="text-xl font-black text-emerald-600">{{ $kpiEntregadosHoy }}</span>
            </div>
            <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg text-lg">✅</div>
        </div>

        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Atrasados / Pend.</span>
                <span class="text-xl font-black text-rose-600">{{ $kpiPendientesAtrasados }}</span>
            </div>
            <div class="p-2 bg-rose-100 text-rose-700 rounded-lg text-lg">⚠️</div>
        </div>
    </div>

    <!-- SECCIÓN 1: TABLA PRINCIPAL DEL DÍA -->
    <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
        
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-4">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Gestión de Envíos por Fecha</h3>
                <p class="text-[11px] text-slate-500">Mostrando entregas programadas para el día seleccionado</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <input 
                    type="date" 
                    wire:model.live="filtroFecha" 
                    class="bg-slate-50 border-slate-300/80 rounded-lg text-xs font-semibold text-slate-800 shadow-xs focus:border-indigo-500"
                >

                <button 
                    type="button"
                    wire:click="$set('filtroFecha', '{{ now()->format('Y-m-d') }}')"
                    class="px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                >
                    Ver Hoy
                </button>

                <select wire:model.live="filtroEstado" class="bg-slate-50 border-slate-300/80 rounded-lg text-xs font-semibold text-slate-800 shadow-xs focus:border-indigo-500">
                    <option value="todos">Todos los Estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="en_camino">En Camino</option>
                    <option value="entregado">Entregados</option>
                    <option value="cancelado">Cancelados</option>
                </select>

                <input 
                    type="text" 
                    wire:model.live.debounce.200ms="busqueda" 
                    placeholder="Buscar cliente, dirección..." 
                    class="bg-slate-50 border-slate-300/80 rounded-lg text-xs text-slate-800 shadow-xs focus:border-indigo-500 placeholder-slate-400"
                >
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-200/60 rounded-xl bg-white shadow-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Venta / Cliente</th>
                        <th class="px-4 py-3">Dirección de Entrega</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-center">Fecha Entrega</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-xs">
                    @forelse($enviosDelDia as $envio)
                        <tr wire:key="envio-dia-{{ $envio->id }}" class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 block">Venta #{{ $envio->venta_id }}</span>
                                <span class="text-slate-600 text-[11px]">
                                    {{ $envio->venta->cliente_nombre ?? 'Cliente sin nombre' }}
                                </span>
                                @if($envio->venta->cliente_telefono)
                                    <span class="text-[10px] text-slate-400 block">Tel: {{ $envio->venta->cliente_telefono }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($envio->direccion)
                                    <div class="font-bold text-slate-800">
                                        {{ $envio->direccion->direccion }} {{ $envio->direccion->numero_puerta }}
                                        @if($envio->direccion->apartamento)
                                            <span class="font-normal text-slate-500">({{ $envio->direccion->apartamento }})</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        {{ $envio->direccion->barrio ? 'B° ' . $envio->direccion->barrio . ' - ' : '' }}
                                        {{ $envio->direccion->ciudad }}
                                        @if($envio->direccion->esquina)
                                            <span class="italic text-slate-400 block">esq. {{ $envio->direccion->esquina }}</span>
                                        @endif
                                    </div>
                                    @if($envio->direccion->indicaciones)
                                        <div class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200/60 rounded px-1.5 py-0.5 mt-1 inline-block">
                                            Obs: {{ $envio->direccion->indicaciones }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Dirección no registrada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($envio->estado === 'pendiente')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                        Pendiente
                                    </span>
                                @elseif($envio->estado === 'en_camino')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        En Camino
                                    </span>
                                @elseif($envio->estado === 'entregado')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Entregado
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">
                                        {{ ucfirst($envio->estado) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600 text-[11px]">
                                @if($envio->fecha_entrega)
                                    <span class="font-bold text-slate-800 block">{{ \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i') }}</span>
                                    @if($envio->cadete_o_transporte)
                                        <span class="text-[10px] text-slate-400">Por: {{ $envio->cadete_o_transporte }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <button 
                                    wire:click="verEnvio({{ $envio->id }})" 
                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition-colors cursor-pointer"
                                    title="Ver Orden de Envío e Imprimir"
                                >
                                    📄 Orden de Envío
                                </button>

                                @if($envio->estado === 'pendiente')
                                    <button 
                                        wire:click="marcarEnCamino({{ $envio->id }})" 
                                        class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-[11px] font-bold transition-colors cursor-pointer"
                                    >
                                        🚚 En Camino
                                    </button>
                                @endif

                                @if($envio->estado !== 'entregado')
                                    <button 
                                        wire:click="abrirModalEntrega({{ $envio->id }})" 
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold shadow-xs transition-colors cursor-pointer"
                                    >
                                        ✓ Entregado
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                                No se encontraron envíos para la fecha seleccionada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $enviosDelDia->links() }}
        </div>
    </div>

    <!-- SECCIÓN 2: BANDEJA DE PENDIENTES OTROS DÍAS -->
    @if(count($enviosPendientesOtrosDias) > 0)
        <div class="bg-amber-50/60 border border-amber-200/80 p-6 rounded-xl shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-amber-200/80 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">📋</span>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">
                            Pendientes y En Camino de Otras Fechas
                        </h3>
                        <p class="text-[11px] text-amber-700">
                            Listado unificado de envíos que requieren atención sin importar la fecha programada
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-200 text-amber-900">
                    {{ count($enviosPendientesOtrosDias) }} pendientes
                </span>
            </div>

            <div class="overflow-x-auto border border-amber-200/60 rounded-xl bg-white shadow-xs max-h-72 overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-amber-100/80 text-[11px] font-bold text-amber-900 uppercase tracking-wider z-10">
                        <tr>
                            <th class="px-4 py-2.5">Fecha Pactada</th>
                            <th class="px-4 py-2.5">Venta / Cliente</th>
                            <th class="px-4 py-2.5">Dirección</th>
                            <th class="px-4 py-2.5 text-center">Estado</th>
                            <th class="px-4 py-2.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100 text-xs">
                        @foreach($enviosPendientesOtrosDias as $envioPend)
                            @php
                                $esAtrasado = $envioPend->fecha_pactada && \Carbon\Carbon::parse($envioPend->fecha_pactada)->isPast() && !\Carbon\Carbon::parse($envioPend->fecha_pactada)->isToday();
                            @endphp
                            <tr wire:key="envio-pend-{{ $envioPend->id }}" class="hover:bg-amber-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-xs {{ $esAtrasado ? 'text-rose-700' : 'text-slate-800' }}">
                                        {{ $envioPend->fecha_pactada ? \Carbon\Carbon::parse($envioPend->fecha_pactada)->format('d/m/Y') : 'Sin fecha' }}
                                    </span>
                                    @if($esAtrasado)
                                        <span class="text-[10px] font-black text-rose-600 block">⚠️ ATRASADO</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    Venta #{{ $envioPend->venta_id }}
                                    <span class="text-[11px] font-normal text-slate-500 block">
                                        {{ $envioPend->venta->cliente_nombre ?? 'Cliente sin nombre' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $envioPend->direccion->direccion ?? 'Sin dirección' }} {{ $envioPend->direccion->numero_puerta ?? '' }}
                                    <span class="text-[10px] text-slate-400 block">{{ $envioPend->direccion->barrio ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($envioPend->estado === 'pendiente')
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">
                                            Pendiente
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-800">
                                            En Camino
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-1">
                                    <button 
                                        wire:click="verEnvio({{ $envioPend->id }})" 
                                        class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition-colors cursor-pointer"
                                    >
                                        📄 Orden
                                    </button>
                                    <button 
                                        wire:click="abrirModalEntrega({{ $envioPend->id }})" 
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition-colors cursor-pointer"
                                    >
                                        ✓ Registrar Entrega
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- MODAL 1: ORDEN DE ENVÍO PARA TRANSPORTISTA (SIN PRECIOS) -->
    @if($mostrarModalVerEnvio && $envioDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl p-6 space-y-6 animate-in fade-in zoom-in-95 duration-150">
                
                <!-- Encabezado Modal (No se imprime) -->
                <div class="flex justify-between items-center border-b border-slate-200 pb-4 print:hidden">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        Vista Previa - Orden de Envío
                    </h3>
                    <div class="flex gap-2">
                        <button 
                            type="button" 
                            onclick="window.print()" 
                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5"
                        >
                            🖨️ Imprimir Orden
                        </button>
                        <button wire:click="cerrarModalVerEnvio" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                    </div>
                </div>

                <!-- DOCUMENTO IMPRIMIBLE (ORDEN DE ENVÍO) -->
                <div class="space-y-6 text-slate-800 p-2">
                    
                    <!-- Encabezado de la Orden -->
                    <div class="flex justify-between items-start border-b-2 border-slate-800 pb-4">
                        <div>
                            <h2 class="text-xl font-black tracking-wide text-slate-900">ORDEN DE ENVÍO</h2>
                            <p class="text-xs text-slate-500 font-bold mt-0.5">Envío #{{ $envioDetalle->id }} | Venta #{{ $envioDetalle->venta_id }}</p>
                        </div>
                        <div class="text-right text-xs">
                            <span class="block text-slate-500 font-semibold uppercase text-[10px]">Fecha Pactada</span>
                            <span class="font-black text-sm text-slate-900">
                                {{ $envioDetalle->fecha_pactada ? \Carbon\Carbon::parse($envioDetalle->fecha_pactada)->format('d/m/Y') : 'Inmediata' }}
                            </span>
                        </div>
                    </div>

                    <!-- Datos del Destinatario y Dirección -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-300 space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-0.5">Destinatario</span>
                                <p class="font-black text-slate-900 text-sm">{{ $envioDetalle->venta->cliente_nombre ?? 'Cliente sin nombre' }}</p>
                                <p class="text-slate-700 font-semibold mt-1">Teléfono: <span class="font-black text-slate-900">{{ $envioDetalle->venta->cliente_telefono ?? 'Sin teléfono' }}</span></p>
                            </div>

                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-0.5">Dirección de Entrega</span>
                                @if($envioDetalle->direccion)
                                    <p class="font-black text-slate-900 text-xs">
                                        {{ $envioDetalle->direccion->direccion }} {{ $envioDetalle->direccion->numero_puerta }}
                                        @if($envioDetalle->direccion->apartamento)
                                            <span class="font-bold">({{ $envioDetalle->direccion->apartamento }})</span>
                                        @endif
                                    </p>
                                    @if($envioDetalle->direccion->esquina)
                                        <p class="text-slate-600 font-medium italic">esq. {{ $envioDetalle->direccion->esquina }}</p>
                                    @endif
                                    <p class="text-slate-700 font-medium">
                                        {{ $envioDetalle->direccion->barrio ? 'B° '.$envioDetalle->direccion->barrio.', ' : '' }}
                                        {{ $envioDetalle->direccion->ciudad }}
                                    </p>
                                @else
                                    <p class="text-slate-400 italic">Sin datos de dirección</p>
                                @endif
                            </div>
                        </div>

                        <!-- Indicaciones especiales destacadas -->
                        @if($envioDetalle->direccion && $envioDetalle->direccion->indicaciones)
                            <div class="bg-amber-100/70 p-3 rounded-lg border border-amber-300/80 mt-2">
                                <span class="text-[10px] font-black uppercase text-amber-900 block tracking-wider">⚠️ Indicaciones / Referencias para el Flete:</span>
                                <p class="text-amber-950 font-bold text-xs mt-0.5">{{ $envioDetalle->direccion->indicaciones }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Lista de Productos (SOLO CANTIDADES Y DESCRIPCIÓN) -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Detalle de Productos a Entregar</h4>
                        <table class="w-full text-left border-collapse border border-slate-300 text-xs">
                            <thead class="bg-slate-200 font-bold uppercase text-[10px] text-slate-700">
                                <tr>
                                    <th class="p-2.5 border border-slate-300 text-center w-16">Cant.</th>
                                    <th class="p-2.5 border border-slate-300">Descripción del Producto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-300">
                                @if($envioDetalle->venta && $envioDetalle->venta->detalles)
                                    @foreach($envioDetalle->venta->detalles as $det)
                                        <tr>
                                            <td class="p-2.5 border border-slate-300 font-black text-center text-sm bg-slate-100 text-slate-900">{{ $det->cantidad }}</td>
                                            <td class="p-2.5 border border-slate-300 font-bold text-slate-800">
                                                {{ $det->producto->nombre ?? 'Producto descontinuado' }}
                                                @if(isset($det->producto->marca) || isset($det->producto->modelo))
                                                    <span class="text-[10px] text-slate-500 block font-normal">
                                                        {{ $det->producto->marca ?? '' }} {{ $det->producto->modelo ? '- Model: '.$det->producto->modelo : '' }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Espacio para Conforme de Recepción / Transporte -->
                    <div class="border-t-2 border-dashed border-slate-400 pt-10 mt-8 grid grid-cols-2 gap-8 text-center text-[10px] text-slate-500">
                        <div>
                            <div class="border-b border-slate-400 mb-1 w-4/5 mx-auto"></div>
                            <span class="font-bold text-slate-700 block">Firma del Transportista / Cadete</span>
                        </div>
                        <div>
                            <div class="border-b border-slate-400 mb-1 w-4/5 mx-auto"></div>
                            <span class="font-bold text-slate-700 block">Conforme del Cliente / Destinatario</span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">Firma y C.I.</span>
                        </div>
                    </div>

                </div>

                <!-- Pie de Modal (No se imprime) -->
                <div class="flex justify-end border-t border-slate-100 pt-3 print:hidden">
                    <button 
                        type="button" 
                        wire:click="cerrarModalVerEnvio" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                    >
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- MODAL 2: CONFIRMAR ENTREGA -->
    @if($mostrarModalEntrega)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        Confirmar Entrega del Envío
                    </h3>
                    <button wire:click="cerrarModal" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <x-input-label value="Fecha y Hora Real de Entrega *" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <input 
                            type="datetime-local" 
                            wire:model="fechaEntregaModal" 
                            class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:border-indigo-500"
                        >
                    </div>

                    <div>
                        <x-input-label value="Cadete / Cadetería / Agencia" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <x-text-input 
                            type="text" 
                            wire:model="cadeteModal" 
                            placeholder="Ej: Cadete Juan / DAC / Moto propia" 
                            class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs"
                        />
                    </div>

                    <div>
                        <x-input-label value="Observaciones / Notas de Entrega" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <textarea 
                            wire:model="notasEntregaModal" 
                            rows="2" 
                            placeholder="Ej: Recibió en puerta. Firma de conformidad." 
                            class="w-full bg-slate-50 border-slate-300 rounded-lg text-xs text-slate-800 focus:border-indigo-500 p-2"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button 
                        type="button" 
                        wire:click="cerrarModal" 
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="confirmarEntrega" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        Guardar Entrega
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>