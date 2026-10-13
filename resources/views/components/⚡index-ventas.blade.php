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
    <!-- Filtro por Fecha Reactivo -->
    <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-3">
            <label for="fecha" class="text-sm font-bold text-gray-700">Filtrar Fecha:</label>
            <input 
                type="date" 
                wire:model.live="fecha" 
                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
            <button 
                type="button" 
                wire:click="setHoy" 
                class="px-3 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md text-xs font-semibold"
            >
                Hoy
            </button>
        </div>
        <span class="text-sm text-gray-500 font-medium">
            Operaciones del día: <strong class="text-gray-800">{{ $cantidadVentas }}</strong>
        </span>
    </div>

    <!-- Tarjetas con Desglose Financiero -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total General -->
        <div class="bg-indigo-600 p-4 rounded-lg shadow-sm text-white">
            <span class="text-xs uppercase font-bold opacity-80 block">Total Vendido</span>
            <span class="text-2xl font-black">$ {{ number_format($totalVendido, 2) }}</span>
        </div>

        <!-- Efectivo / Contado -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
            <span class="text-xs text-gray-500 uppercase font-bold block">Efectivo / Contado</span>
            <span class="text-xl font-extrabold text-gray-800">$ {{ number_format($totalEfectivo, 2) }}</span>
        </div>

        <!-- Tarjeta de Crédito -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
            <span class="text-xs text-gray-500 uppercase font-bold block">Crédito</span>
            <span class="text-xl font-extrabold text-gray-800">$ {{ number_format($totalTarjeta, 2) }}</span>
        </div>

        <!-- Tarjeta de Débito -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
            <span class="text-xs text-gray-500 uppercase font-bold block">Débito</span>
            <span class="text-xl font-extrabold text-gray-800">$ {{ number_format($totalDebito, 2) }}</span>
        </div>

        <!-- Transferencia -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
            <span class="text-xs text-gray-500 uppercase font-bold block">Transferencia</span>
            <span class="text-xl font-extrabold text-gray-800">$ {{ number_format($totalTransfer, 2) }}</span>
        </div>

    </div>

    <!-- Tabla de Historial -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                        <th class="px-6 py-3 text-left"># Venta</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Hora</th>
                        <th class="px-6 py-3 text-left">Método Pago</th>
                        <th class="px-6 py-3 text-left">Entrega</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($ventas as $venta)
                        <tr wire:key="venta-{{ $venta->id }}">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">#{{ $venta->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $venta->cliente_nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $venta->fecha_compra ? \Carbon\Carbon::parse($venta->fecha_compra)->format('H:i A') : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                                {{ $venta->metodo_pago }} {{ $venta->cuotas > 1 ? "({$venta->cuotas}x)" : '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $venta->tipo_entrega === 'flete' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($venta->tipo_entrega) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-indigo-600">$ {{ number_format($venta->precio_total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('ventas.show', $venta) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    Ver Recibo
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No hay ventas registradas para el día {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $ventas->links() }}
            </div>
        </div>
    </div>
</div>