<?php

use Livewire\Component;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Envio;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $mesSeleccionado;
    public $anioSeleccionado;

    public function mount()
    {
        // Por defecto arranca en el mes y año actual
        $this->mesSeleccionado = Carbon::now()->month;
        $this->anioSeleccionado = Carbon::now()->year;
    }

    public function irAlMesActual()
    {
        $this->mesSeleccionado = Carbon::now()->month;
        $this->anioSeleccionado = Carbon::now()->year;
    }

    public function with(): array
    {
        $fechaInicio = Carbon::createFromDate($this->anioSeleccionado, $this->mesSeleccionado, 1)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        // 1. Consultar Ventas del Mes
        $ventasMes = Venta::whereBetween('fecha_compra', [$fechaInicio, $fechaFin])
            ->where('estado', '!=', 'cancelada')
            ->get();

        $totalVentas = $ventasMes->sum('precio_total');
        $cantidadVentas = $ventasMes->count();
        $ticketPromedio = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;
        $totalRecaudadoFlete = $ventasMes->sum('costo_flete');

        // 2. Cálculo de Ganancia Estimada (Subtotal Venta - Costo Productos)
        $idsVentas = $ventasMes->pluck('id');
        
        $detalles = DetalleVenta::with('producto')
            ->whereIn('venta_id', $idsVentas)
            ->get();

        $costoTotalProductos = $detalles->sum(function ($item) {
            $costoUnitario = $item->producto->precio_costo ?? $item->producto->costo ?? 0;
            return $costoUnitario * $item->cantidad;
        });

        // Ganancia = (Total Ventas - Fletes) - Costo de Productos
        // (El flete se excluye o se incluye según si tenés costo de cadetería externa)
        $subtotalBrutoVentas = $ventasMes->sum('subtotal') - $ventasMes->sum('descuento');
        $gananciaNeta = $subtotalBrutoVentas - $costoTotalProductos;

        // 3. Envíos del Mes
        $enviosMes = Envio::whereHas('venta', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_compra', [$fechaInicio, $fechaFin]);
        })->get();

        $totalEnvios = $enviosMes->count();
        $enviosEntregados = $enviosMes->where('estado', 'entregado')->count();
        $enviosPendientes = $enviosMes->whereIn('estado', ['pendiente', 'en_camino'])->count();

        // 4. Métodos de Pago
        $metodosPago = Venta::select('metodo_pago', DB::raw('count(*) as total'), DB::raw('sum(precio_total) as monto'))
            ->whereBetween('fecha_compra', [$fechaInicio, $fechaFin])
            ->where('estado', '!=', 'cancelada')
            ->groupBy('metodo_pago')
            ->get();

        // 5. Top 5 Productos más Vendidos del Mes
        $topProductos = DetalleVenta::select('producto_id', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(subtotal) as total_monto'))
            ->whereIn('venta_id', $idsVentas)
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->with('producto')
            ->take(5)
            ->get();

        return [
            'totalVentas'         => $totalVentas,
            'gananciaNeta'        => $gananciaNeta,
            'cantidadVentas'      => $cantidadVentas,
            'ticketPromedio'      => $ticketPromedio,
            'totalEnvios'         => $totalEnvios,
            'enviosEntregados'    => $enviosEntregados,
            'enviosPendientes'    => $enviosPendientes,
            'totalRecaudadoFlete' => $totalRecaudadoFlete,
            'metodosPago'         => $metodosPago,
            'topProductos'        => $topProductos,
            'nombreMes'           => $fechaInicio->translatedFormat('F'),
        ];
    }
};
?>

<div class="space-y-6">

    <!-- BARRA SUPERIOR Y FILTRO POR MES/AÑO -->
    <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-black uppercase tracking-wider text-slate-800">
                Resumen Ejecutivo - {{ ucfirst($nombreMes) }} {{ $anioSeleccionado }}
            </h2>
            <p class="text-xs text-slate-500">Métricas clave de rendimiento comercial y logístico</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Selector de Mes -->
            <select wire:model.live="mesSeleccionado" class="bg-slate-50 border-slate-300/80 rounded-lg text-xs font-semibold text-slate-800 shadow-xs focus:border-indigo-500">
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

            <!-- Selector de Año -->
            <select wire:model.live="anioSeleccionado" class="bg-slate-50 border-slate-300/80 rounded-lg text-xs font-semibold text-slate-800 shadow-xs focus:border-indigo-500">
                @for($a = date('Y'); $a >= date('Y') - 4; $a--)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endfor
            </select>

            <!-- Botón Ir al Mes Actual -->
            <button 
                type="button" 
                wire:click="irAlMesActual" 
                class="px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg text-xs font-bold transition-colors cursor-pointer"
            >
                Mes Actual
            </button>
        </div>
    </div>

    <!-- TARJETAS DE PRINCIPALES (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Facturado -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Total Facturado</span>
                <span class="text-2xl font-black text-indigo-900">$ {{ number_format($totalVentas, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $cantidadVentas }} ventas registradas</span>
            </div>
            <div class="p-3 bg-indigo-100 text-indigo-700 rounded-xl text-xl">💰</div>
        </div>

        <!-- Ganancia Estimada -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Ganancia Bruta Est.</span>
                <span class="text-2xl font-black text-emerald-700">$ {{ number_format($gananciaNeta, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Margen sobre costo directo</span>
            </div>
            <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl text-xl">📈</div>
        </div>

        <!-- Ticket Promedio -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Ticket Promedio</span>
                <span class="text-2xl font-black text-slate-800">$ {{ number_format($ticketPromedio, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Promedio por venta</span>
            </div>
            <div class="p-3 bg-slate-200 text-slate-700 rounded-xl text-xl">🛒</div>
        </div>

        <!-- Logística y Envíos -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Envíos del Mes</span>
                <span class="text-2xl font-black text-amber-700">{{ $totalEnvios }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Recaudado flete: ${{ number_format($totalRecaudadoFlete, 2) }}</span>
            </div>
            <div class="p-3 bg-amber-100 text-amber-700 rounded-xl text-xl">🚚</div>
        </div>

    </div>

    <!-- SECCIÓN SECUNDARIA: DETALLE DE PAGO Y TOP PRODUCTOS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 1. Desglose de Métodos de Pago -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">
                Ingresos por Método de Pago
            </h3>

            <div class="space-y-3">
                @forelse($metodosPago as $metodo)
                    @php
                        $porcentaje = $totalVentas > 0 ? ($metodo->monto / $totalVentas) * 100 : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-800">
                            <span>{{ $metodo->metodo_pago }}</span>
                            <span>$ {{ number_format($metodo->monto, 2) }} ({{ number_format($porcentaje, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">Sin datos registrados este mes.</p>
                @endforelse
            </div>
        </div>

        <!-- 2. Top 5 Productos más Vendidos -->
        <div class="lg:col-span-2 bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">
                Top 5 Productos Más Vendidos
            </h3>

            <div class="overflow-x-auto border border-slate-200/60 rounded-xl bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Producto</th>
                            <th class="px-4 py-2.5 text-center">Unidades Vendidas</th>
                            <th class="px-4 py-2.5 text-right">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 text-xs">
                        @forelse($topProductos as $top)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 font-bold text-slate-800">
                                    {{ $top->producto->nombre ?? 'Producto Eliminado' }}
                                    @if(isset($top->producto->marca))
                                        <span class="text-[10px] text-slate-400 font-normal block">{{ $top->producto->marca }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-black text-indigo-700">
                                    {{ $top->total_cantidad }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900">
                                    $ {{ number_format($top->total_monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400 text-xs">
                                    No hay ventas registradas en el período seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>