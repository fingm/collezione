<?php

use App\Models\Usuario;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $buscar = '';
    public $filtroRol = '';

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingFiltroRol()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Usuario::with('role')
            ->when($this->buscar, function ($q) {
                $term = '%' . $this->buscar . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nombre', 'like', $term)
                        ->orWhere('apellido', 'like', $term)
                        ->orWhere('documento', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($this->filtroRol, function ($q) {
                $q->where('role_id', $this->filtroRol);
            });

        return [
            'usuarios' => $query->latest()->paginate(10),
            'roles'    => Role::all(),
        ];
    }
};
?>

<div class="space-y-6">

    <!-- Filtro de Búsqueda y Rol -->
    <div class="bg-slate-100/70 border border-slate-200/60 p-5 rounded-xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
            <!-- Buscador -->
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="buscar" 
                    placeholder="Buscar por nombre, documento o email..." 
                    class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border-slate-300/80 rounded-lg shadow-sm focus:border-indigo-500 text-slate-800 placeholder-slate-400"
                >
            </div>

            <!-- Filtro por Rol -->
            <select wire:model.live="filtroRol" class="w-full sm:w-auto py-2 px-3 bg-slate-50 border-slate-300/80 rounded-lg text-xs font-semibold text-slate-700 focus:border-indigo-500">
                <option value="">Todos los Roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}">{{ ucfirst($r->nombre) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Usuario / Cliente</th>
                        <th class="px-6 py-3.5">Contacto</th>
                        <th class="px-6 py-3.5">Rol</th>
                        <th class="px-6 py-3.5">Estado</th>
                        <th class="px-6 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-xs">
                    @forelse ($usuarios as $u)
                        <tr wire:key="user-{{ $u->id }}" class="hover:bg-indigo-100/30 transition-colors group">
                            <!-- Nombre de usuario como link directo a Ver -->
                            <td class="px-6 py-4">
                                <a href="{{ route('usuarios.show', $u->id) }}" class="font-bold text-slate-900 block hover:text-indigo-700 transition-colors">
                                    {{ $u->nombre_completo }}
                                </a>
                                <span class="text-[11px] text-slate-500">{{ $u->email }}</span>
                            </td>
                            <!-- Teléfono -->
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                {{ $u->telefono ?? '-' }}
                            </td>

                            <!-- Rol -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $u->role?->nombre === 'admin' ? 'bg-purple-100/80 text-purple-800 border border-purple-200' : '' }}
                                    {{ $u->role?->nombre === 'vendedor' ? 'bg-sky-100/80 text-sky-800 border border-sky-200' : '' }}
                                    {{ $u->role?->nombre === 'cliente' ? 'bg-slate-200 text-slate-700' : '' }}">
                                    {{ $u->role?->nombre ?? 'Sin rol' }}
                                </span>
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $u->activo ? 'bg-emerald-100/80 text-emerald-800 border border-emerald-200' : 'bg-red-100/80 text-red-800 border border-red-200' }}">
                                    {{ $u->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <!-- Botón de Acción Ver -->
                            <td class="px-6 py-4 text-right">
                                <a 
                                    href="{{ route('usuarios.show', $u->id) }}" 
                                    class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-200/80 hover:bg-slate-300 px-3 py-1.5 rounded-lg transition-colors"
                                >
                                    🔍 Ver Ficha
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <p class="text-xs font-medium text-slate-500">No se encontraron usuarios o clientes registrados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($usuarios->hasPages())
            <div class="px-6 py-4 bg-slate-200/40 border-t border-slate-200 flex items-center justify-between">
                @if ($usuarios->onFirstPage())
                    <span class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-200/60 rounded-lg cursor-not-allowed select-none">&larr; Anterior</span>
                @else
                    <button wire:click="previousPage" class="px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors cursor-pointer">&larr; Anterior</button>
                @endif

                <span class="text-xs text-slate-600 font-medium">
                    Página <strong class="text-slate-800 font-bold">{{ $usuarios->currentPage() }}</strong> de <strong class="text-slate-800 font-bold">{{ $usuarios->lastPage() }}</strong>
                </span>

                @if ($usuarios->hasMorePages())
                    <button wire:click="nextPage" class="px-3.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors cursor-pointer">Siguiente &rarr;</button>
                @else
                    <span class="px-3.5 py-1.5 text-xs font-semibold text-slate-400 bg-slate-200/60 rounded-lg cursor-not-allowed select-none">Siguiente &rarr;</span>
                @endif
            </div>
        @endif
    </div>

</div>