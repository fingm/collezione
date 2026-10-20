<?php

use Livewire\Component;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Envio;
use App\Models\Gasto;
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

        // 2. Cálculo de Costos de Productos Vendidos y Ganancia Bruta
        $idsVentas = $ventasMes->pluck('id');
        
        $detalles = DetalleVenta::with('producto')
            ->whereIn('venta_id', $idsVentas)
            ->get();

        $costoTotalProductos = $detalles->sum(function ($item) {
            $costoUnitario = $item->producto->precio_costo ?? $item->producto->costo ?? 0;
            return $costoUnitario * $item->cantidad;
        });

        // Ganancia Bruta = (Subtotal de Ventas - Descuentos) - Costo Directo de Productos
        $subtotalBrutoVentas = $ventasMes->sum('subtotal') - $ventasMes->sum('descuento');
        $gananciaBruta = $subtotalBrutoVentas - $costoTotalProductos;

        // 3. Consultar Gastos Operativos del Mes
        $totalGastosMes = Gasto::whereYear('fecha', $this->anioSeleccionado)
            ->whereMonth('fecha', $this->mesSeleccionado)
            ->sum('monto');

        // Ganancia Neta Real = Ganancia Bruta - Gastos Operativos
        $gananciaNetaReal = $gananciaBruta - $totalGastosMes;

        // Desglose de Gastos por Categoría
        $gastosPorCategoria = Gasto::select('categoria', DB::raw('SUM(monto) as total_monto'))
            ->whereYear('fecha', $this->anioSeleccionado)
            ->whereMonth('fecha', $this->mesSeleccionado)
            ->groupBy('categoria')
            ->orderByDesc('total_monto')
            ->get();

        // 4. Envíos del Mes
        $enviosMes = Envio::whereHas('venta', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_compra', [$fechaInicio, $fechaFin]);
        })->get();

        $totalEnvios = $enviosMes->count();
        $enviosEntregados = $enviosMes->where('estado', 'entregado')->count();
        $enviosPendientes = $enviosMes->whereIn('estado', ['pendiente', 'en_camino'])->count();

        // 5. Métodos de Pago
        $metodosPago = Venta::select('metodo_pago', DB::raw('count(*) as total'), DB::raw('sum(precio_total) as monto'))
            ->whereBetween('fecha_compra', [$fechaInicio, $fechaFin])
            ->where('estado', '!=', 'cancelada')
            ->groupBy('metodo_pago')
            ->get();

        // 6. Top 5 Productos más Vendidos del Mes
        $topProductos = DetalleVenta::select('producto_id', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(subtotal) as total_monto'))
            ->whereIn('venta_id', $idsVentas)
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->with('producto')
            ->take(5)
            ->get();

        return [
            'totalVentas'         => $totalVentas,
            'gananciaBruta'       => $gananciaBruta,
            'totalGastosMes'      => $totalGastosMes,
            'gananciaNetaReal'    => $gananciaNetaReal,
            'gastosPorCategoria'  => $gastosPorCategoria,
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
            <p class="text-xs text-slate-500">Métricas clave de rendimiento financiero, comercial y logístico</p>
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

    <!-- TARJETAS PRINCIPALES DE KPIs FINANCIEROS -->
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

        <!-- Total Gastos del Mes -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Gastos Operativos</span>
                <span class="text-2xl font-black text-rose-700">$ {{ number_format($totalGastosMes, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Egresos registrados</span>
            </div>
            <div class="p-3 bg-rose-100 text-rose-700 rounded-xl text-xl">💸</div>
        </div>

        <!-- Ganancia Neta Real (Ganancia Bruta - Gastos) -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Ganancia Neta Real</span>
                <span class="text-2xl font-black {{ $gananciaNetaReal >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    $ {{ number_format($gananciaNetaReal, 2) }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Margen neto de operación</span>
            </div>
            <div class="p-3 {{ $gananciaNetaReal >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} rounded-xl text-xl">
                {{ $gananciaNetaReal >= 0 ? '📈' : '📉' }}
            </div>
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

    <!-- SECCIÓN SECUNDARIA: MÉTODO DE PAGO, GASTOS POR CATEGORÍA Y TOP PRODUCTOS -->
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

        <!-- 2. Gastos por Categoría -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">
                Egresos por Categoría
            </h3>

            <div class="space-y-3">
                @forelse($gastosPorCategoria as $gCat)
                    @php
                        $porcentajeGasto = $totalGastosMes > 0 ? ($gCat->total_monto / $totalGastosMes) * 100 : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-800">
                            <span>{{ $gCat->categoria }}</span>
                            <span class="text-red-700">$ {{ number_format($gCat->total_monto, 2) }} ({{ number_format($porcentajeGasto, 1) }}%)</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $porcentajeGasto }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">Sin gastos registrados en este período.</p>
                @endforelse
            </div>
        </div>

        <!-- 3. Top 5 Productos más Vendidos -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">
                Top 5 Productos Más Vendidos
            </h3>

            <div class="overflow-x-auto border border-slate-200/60 rounded-xl bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Producto</th>
                            <th class="px-3 py-2 text-center">Cant.</th>
                            <th class="px-3 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 text-xs">
                        @forelse($topProductos as $top)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2.5 font-bold text-slate-800">
                                    {{ $top->producto->nombre ?? 'Producto Eliminado' }}
                                </td>
                                <td class="px-3 py-2.5 text-center font-black text-indigo-700">
                                    {{ $top->total_cantidad }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-bold text-slate-900">
                                    $ {{ number_format($top->total_monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-slate-400 text-xs">
                                    Sin ventas en este período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>