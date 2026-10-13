<?php

use App\Models\Producto;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $buscar = '';

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'productos' => Producto::when($this->buscar, function ($query) {
                return $query->where('nombre', 'like', '%' . $this->buscar . '%')
                             ->orWhere('marca', 'like', '%' . $this->buscar . '%')
                             ->orWhere('modelo', 'like', '%' . $this->buscar . '%');
            })->latest()->paginate(10),
        ];
    }
};
?>
<div>
    <!-- Filtro de Búsqueda -->
    <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="buscar" 
                    placeholder="Buscar por nombre, marca o modelo..." 
                    class="pl-10 pr-4 py-2.5 text-sm bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg shadow-sm w-full transition-all duration-150 placeholder-slate-400"
                >
            </div>
            
            <div class="text-xs text-slate-500 font-medium self-end sm:self-center">
                Mostrando <span class="font-bold text-slate-800">{{ $productos->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $productos->lastItem() ?? 0 }}</span> de <span class="font-bold text-slate-800">{{ $productos->total() }}</span> productos
            </div>
        </div>
    </div>

    <!-- Tabla de Productos en Tono Suave -->
    <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Producto</th>
                        <th class="px-6 py-3.5">Marca / Modelo</th>
                        <th class="px-6 py-3.5">Precio Venta</th>
                        <th class="px-6 py-3.5">Stock</th>
                        <th class="px-6 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-sm">
                    @forelse ($productos as $producto)
                        <tr wire:key="prod-{{ $producto->id }}" class="hover:bg-indigo-100/40 transition-colors duration-150 group">
                            <!-- Nombre -->
                            <td class="px-6 py-4 font-semibold text-slate-800 group-hover:text-indigo-700 transition-colors">
                                {{ $producto->nombre }}
                            </td>
                            
                            <!-- Marca / Modelo -->
                            <td class="px-6 py-4 text-slate-600">
                                @if($producto->marca)
                                    <span class="font-medium text-slate-800">{{ $producto->marca }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif

                                @if($producto->modelo)
                                    <span class="text-xs text-slate-500 block sm:inline sm:ml-1">({{ $producto->modelo }})</span>
                                @endif
                            </td>
                            
                            <!-- Precio -->
                            <td class="px-6 py-4 font-bold text-slate-900">
                                $ {{ number_format($producto->precio_venta, 2) }}
                            </td>
                            
                            <!-- Stock -->
                            <td class="px-6 py-4">
                                @if($producto->stock === null)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-700">
                                        Sin control
                                    </span>
                                @elseif($producto->stock <= 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100/80 text-red-800 border border-red-200">
                                        Sin Stock (0)
                                    </span>
                                @elseif($producto->stock <= 5)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-800 border border-amber-200">
                                        Bajo ({{ $producto->stock }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100/80 text-emerald-800 border border-emerald-200">
                                        {{ $producto->stock }} un.
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('productos.show', $producto->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-100/80 hover:bg-indigo-200 px-3 py-1.5 rounded-lg transition-all duration-150">
                                    <span>Ver</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-sm font-medium text-slate-500">No se encontraron productos coincidentes.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($productos->hasPages())
            <div class="px-6 py-4 bg-slate-200/40 border-t border-slate-200 flex items-center justify-between">
                @if ($productos->onFirstPage())
                    <span class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-200/60 rounded-lg cursor-not-allowed select-none">
                        &larr; Anterior
                    </span>
                @else
                    <button wire:click="previousPage" class="px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors cursor-pointer">
                        &larr; Anterior
                    </button>
                @endif

                <span class="text-xs text-slate-600 font-medium">
                    Página <strong class="text-slate-800 font-bold">{{ $productos->currentPage() }}</strong> de <strong class="text-slate-800 font-bold">{{ $productos->lastPage() }}</strong>
                </span>

                @if ($productos->hasMorePages())
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