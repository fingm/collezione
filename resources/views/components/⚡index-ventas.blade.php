<?php

use App\Models\Venta;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    public $fecha;

    public function mount()
    {
        $this->fecha = Carbon::today()->format('Y-m-d');
    }

    public function updatingFecha()
    {
        $this->resetPage();
    }

    public function setHoy()
    {
        $this->fecha = Carbon::today()->format('Y-m-d');
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Venta::whereDate('fecha_compra', $this->fecha);

        return [
            'ventas' => (clone $query)->latest('fecha_compra')->paginate(10),
            'cantidadVentas' => (clone $query)->count(),
            'totalVendido' => (clone $query)->sum('precio_total'),
            'totalEfectivo' => (clone $query)->where('metodo_pago', 'Contado')->sum('precio_total'),
            'totalTarjeta' => (clone $query)->where('metodo_pago', 'Tarjeta')->sum('precio_total'),
            'totalDebito' => (clone $query)->where('metodo_pago', 'Débito')->sum('precio_total'),
            'totalTransfer' => (clone $query)->where('metodo_pago', 'Transferencia')->sum('precio_total'),
        ];
    }
};
?>

<div class="space-y-6">

    <!-- Filtro por Fecha Reactivo y Contador -->
    <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="fecha" class="text-xs font-bold uppercase tracking-wider text-slate-600">Filtrar Fecha:</label>
            <input 
                type="date" 
                wire:model.live="fecha" 
                class="text-xs bg-slate-50 border-slate-300/80 rounded-lg shadow-sm focus:border-indigo-500 text-slate-800 placeholder-slate-400""

            >
            <button 
                type="button" 
                wire:click="setHoy" 
                class="px-3 py-1.5 bg-slate-200/80 hover:bg-slate-300/70 text-slate-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
            >
                Hoy
            </button>
        </div>

        <div class="text-xs text-slate-500 font-medium">
            Operaciones del día: <strong class="text-slate-800 text-sm font-bold bg-slate-200/60 px-2 py-0.5 rounded-md">{{ $cantidadVentas }}</strong>
        </div>
    </div>

    <!-- Tarjetas de Desglose Financiero -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total General -->
        <div class="bg-indigo-600 p-4 rounded-xl shadow-sm text-white flex flex-col justify-between">
            <span class="text-[11px] uppercase font-bold tracking-wider opacity-80">Total Vendido</span>
            <span class="text-2xl font-black mt-1">$ {{ number_format($totalVendido, 2) }}</span>
        </div>

        <!-- Efectivo / Contado -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm border-l-4 border-l-emerald-500">
            <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block">Efectivo / Contado</span>
            <span class="text-xl font-extrabold text-slate-800 mt-1 block">$ {{ number_format($totalEfectivo, 2) }}</span>
        </div>

        <!-- Tarjeta de Crédito -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm border-l-4 border-l-sky-500">
            <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block">Crédito</span>
            <span class="text-xl font-extrabold text-slate-800 mt-1 block">$ {{ number_format($totalTarjeta, 2) }}</span>
        </div>

        <!-- Tarjeta de Débito -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm border-l-4 border-l-purple-500">
            <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block">Débito</span>
            <span class="text-xl font-extrabold text-slate-800 mt-1 block">$ {{ number_format($totalDebito, 2) }}</span>
        </div>

        <!-- Transferencia -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-4 rounded-xl shadow-sm border-l-4 border-l-amber-500">
            <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block">Transferencia</span>
            <span class="text-xl font-extrabold text-slate-800 mt-1 block">$ {{ number_format($totalTransfer, 2) }}</span>
        </div>

    </div>

    <!-- Tabla de Historial -->
    <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-6 py-3.5"># Venta</th>
                        <th class="px-6 py-3.5">Cliente</th>
                        <th class="px-6 py-3.5">Hora</th>
                        <th class="px-6 py-3.5">Método Pago</th>
                        <th class="px-6 py-3.5">Entrega</th>
                        <th class="px-6 py-3.5 text-right">Total</th>
                        <th class="px-6 py-3.5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-sm">
                    @forelse ($ventas as $venta)
                        <tr wire:key="venta-{{ $venta->id }}" class="hover:bg-indigo-100/40 transition-colors duration-150 group">
                            
                            <!-- ID Venta -->
                            <td class="px-6 py-4 font-bold text-slate-900 group-hover:text-indigo-700 transition-colors">
                                #{{ $venta->id }}
                            </td>

                            <!-- Cliente -->
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $venta->cliente_nombre }}
                            </td>

                            <!-- Hora -->
                            <td class="px-6 py-4 text-slate-500 text-xs font-semibold">
                                {{ $venta->fecha_compra ? \Carbon\Carbon::parse($venta->fecha_compra)->format('H:i A') : '-' }}
                            </td>

                            <!-- Método de Pago -->
                            <td class="px-6 py-4 text-slate-700 font-medium">
                                {{ $venta->metodo_pago }}
                                @if($venta->cuotas > 1)
                                    <span class="text-xs text-indigo-600 bg-indigo-50 font-bold px-1.5 py-0.5 rounded ml-1">
                                        {{ $venta->cuotas }}x
                                    </span>
                                @endif
                            </td>

                            <!-- Tipo Entrega -->
                            <td class="px-6 py-4">
                                @if($venta->tipo_entrega === 'flete')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100/80 text-sky-800 border border-sky-200">
                                        Flete / Envío
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200/80 text-slate-700">
                                        Retiro Local
                                    </span>
                                @endif
                            </td>

                            <!-- Total -->
                            <td class="px-6 py-4 text-right font-black text-slate-900">
                                $ {{ number_format($venta->precio_total, 2) }}
                            </td>

                            <!-- Acción -->
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('ventas.show', $venta) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-100/80 hover:bg-indigo-200 px-3 py-1.5 rounded-lg transition-all duration-150">
                                    <span>Ver Recibo</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm font-medium text-slate-500">
                                        No hay ventas registradas para el día <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Personalizada Reactiva -->
        @if ($ventas->hasPages())
            <div class="px-6 py-4 bg-slate-200/40 border-t border-slate-200 flex items-center justify-between">
                
                <!-- Botón Anterior -->
                @if ($ventas->onFirstPage())
                    <span class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-200/60 rounded-lg cursor-not-allowed select-none">
                        &larr; Anterior
                    </span>
                @else
                    <button wire:click="previousPage" class="px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors cursor-pointer">
                        &larr; Anterior
                    </button>
                @endif

                <!-- Indicador Central -->
                <span class="text-xs text-slate-600 font-medium">
                    Página <strong class="text-slate-800 font-bold">{{ $ventas->currentPage() }}</strong> de <strong class="text-slate-800 font-bold">{{ $ventas->lastPage() }}</strong>
                </span>

                <!-- Botón Siguiente -->
                @if ($ventas->hasMorePages())
                    <button wire:click="nextPage" class="px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors cursor-pointer">
                        Siguiente &rarr;
                    </button>
                @else
                    <span class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-200/60 rounded-lg cursor-not-allowed select-none">
                        Siguiente &rarr;
                    </span>
                @endif

            </div>
        @endif
    </div>

</div>
