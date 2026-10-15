<?php

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Usuario;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    // Búsqueda de productos en la barra superior
    public $buscarProducto = '';

    // Carrito temporal: array de productos agregados
    public $items = [];

    // Datos del cliente y la transacción
    public $cliente_id = null; // Declarado para vincular con la DB y evitar error
    public $cliente_nombre = '';
    public $cliente_telefono = '';
    public $cliente_direccion = '';
    public $tipo_entrega = 'retiro'; // 'retiro' o 'flete'
    public $costo_flete = 0;
    public $metodo_pago = 'Contado';
    public $cuotas = 1;
    public $descuento = 0;
    public $notas = '';

    // Agregar producto al carrito temporal
    public function agregarProducto($productoId)
    {
        $producto = Producto::find($productoId);

        if (!$producto) return;

        // Si el producto ya está en el carrito, incrementamos la cantidad
        if (isset($this->items[$productoId])) {
            $this->items[$productoId]['cantidad']++;
            $this->items[$productoId]['subtotal'] = $this->items[$productoId]['cantidad'] * $this->items[$productoId]['precio_unitario'];
        } else {
            $this->items[$productoId] = [
                'id'              => $producto->id,
                'nombre'          => $producto->nombre,
                'precio_unitario' => $producto->precio_venta,
                'cantidad'        => 1,
                'subtotal'        => $producto->precio_venta,
                'stock_actual'    => $producto->stock,
            ];
        }

        $this->buscarProducto = ''; // Limpia el buscador
    }

    // Modificar cantidad directamente en el carrito
    public function actualizarCantidad($productoId, $cantidad)
    {
        if ($cantidad <= 0) {
            $this->quitarProducto($productoId);
            return;
        }

        if (isset($this->items[$productoId])) {
            $this->items[$productoId]['cantidad'] = (int) $cantidad;
            $this->items[$productoId]['subtotal'] = (int) $cantidad * $this->items[$productoId]['precio_unitario'];
        }
    }

    // Quitar producto del carrito
    public function quitarProducto($productoId)
    {
        unset($this->items[$productoId]);
    }

    // Propiedades calculadas en tiempo real
    public function getSubtotalProductosProperty()
    {
        return array_sum(array_column($this->items, 'subtotal'));
    }

    public function getTotalFinalProperty()
    {
        $flete = $this->tipo_entrega === 'flete' ? (float) $this->costo_flete : 0;
        return ($this->subtotalProductos + $flete) - (float) $this->descuento;
    }

    // Método público para seleccionar el cliente y autocompletar
    public function seleccionarCliente($id)
    {
        $cliente = Usuario::find($id);

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $nombreCompleto = trim($cliente->nombre . ' ' . ($cliente->apellido ?? ''));
            $this->cliente_nombre = $nombreCompleto;
            $this->cliente_telefono = $cliente->telefono ?? '';
            $this->cliente_direccion = $cliente->direccion ?? '';
        }
    }

    public function agregarPrimerProducto()
    {
        if (strlen(trim($this->buscarProducto)) >= 1) {
            $query = trim($this->buscarProducto);
            
            $producto = Producto::where('activo', true)
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'like', '%' . $query . '%')
                    ->orWhere('marca', 'like', '%' . $query . '%')
                    ->orWhere('modelo', 'like', '%' . $query . '%');
                })
                ->first();

            if ($producto) {
                $this->agregarProducto($producto->id);
            }
        }
    }

    // Guardar la Venta en MySQL
    public function guardarVenta()
    {
        $this->validate([
            'cliente_nombre' => 'required|string|max:255',
            'metodo_pago'    => 'required|string',
            'items'          => 'required|array|min:1',
            'costo_flete'    => 'nullable|numeric|min:0',
            'descuento'      => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Debes agregar al menos un producto a la venta.',
            'cliente_nombre.required' => 'El nombre del cliente es obligatorio.',
        ]);
        
        DB::transaction(function () {
            // 1. Crear la Venta (Cabecera)
            $venta = Venta::create([
                'cliente_id'        => $this->cliente_id,
                'cliente_nombre'    => $this->cliente_nombre,
                'cliente_telefono'  => $this->cliente_telefono,
                'cliente_direccion' => $this->cliente_direccion,
                'tipo_entrega'      => $this->tipo_entrega,
                'costo_flete'       => $this->tipo_entrega === 'flete' ? $this->costo_flete : 0,
                'metodo_pago'       => $this->metodo_pago,
                'cuotas'            => $this->cuotas,
                'subtotal'          => $this->subtotalProductos,
                'descuento'         => $this->descuento,
                'precio_total'      => $this->totalFinal,
                'fecha_compra'      => now(),
                'estado'            => 'completada',
                'notas'             => $this->notas,
            ]);

            // 2. Crear cada DetalleVenta y descontar stock
            foreach ($this->items as $item) {
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $item['id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal'        => $item['subtotal'],
                ]);

                $producto = Producto::find($item['id']);
                if ($producto && $producto->stock !== null) {
                    $producto->decrement('stock', $item['cantidad']);
                }
            }
        });

        session()->flash('status', '¡Venta registrada con éxito!');
        return redirect()->route('ventas.index');
    }

    public function with(): array
    {
        $productosEncontrados = [];

        if (trim($this->buscarProducto) !== '') {
            $query = trim($this->buscarProducto);

            $productosEncontrados = Producto::where('activo', true)
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'like', '%' . $query . '%')
                    ->orWhere('marca', 'like', '%' . $query . '%')
                    ->orWhere('modelo', 'like', '%' . $query . '%');
                })
                ->take(5)
                ->get();
        }

        // Catálogo de productos activos disponibles para elegir directamente
        $catalogoProductos = Producto::where('activo', true)
            ->when(trim($this->buscarProducto) !== '', function ($q) {
                $query = trim($this->buscarProducto);
                $q->where(function ($sub) use ($query) {
                    $sub->where('nombre', 'like', '%' . $query . '%')
                        ->orWhere('marca', 'like', '%' . $query . '%')
                        ->orWhere('modelo', 'like', '%' . $query . '%');
                });
            })
            ->latest()
            ->take(12)
            ->get();

        // Búsqueda en tiempo real de Usuarios filtrados por role_id
        $clientesEncontrados = [];
        $queryCliente = trim($this->cliente_nombre);

        if ($queryCliente !== '') {
            $clientesEncontrados = Usuario::where('role_id', 2)
                ->where(function ($q) use ($queryCliente) {
                    $q->where('nombre', 'like', '%' . $queryCliente . '%')
                    ->orWhere('apellido', 'like', '%' . $queryCliente . '%')
                    ->orWhere('telefono', 'like', '%' . $queryCliente . '%');
                })
                ->take(5)
                ->get();
        }

        return [
            'productosEncontrados' => $productosEncontrados,
            'catalogoProductos'    => $catalogoProductos,
            'clientesEncontrados'  => $clientesEncontrados,
        ];
    }  
};
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- COLUMNA IZQUIERDA: Buscador, Catálogo y Carrito (2 Columnas) -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Buscador de Productos -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Buscar o Seleccionar Productos</h3>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.150ms="buscarProducto" 
                    wire:keydown.enter.prevent="agregarPrimerProducto"
                    placeholder="Escribe el nombre, marca o modelo para filtrar..." 
                    class="w-full pl-9 pr-4 py-2.5 text-sm bg-slate-50 border-slate-300/80 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 placeholder-slate-400"
                >

                <!-- Autocompletado rápido emergente -->
                @if(!empty($productosEncontrados) && count($productosEncontrados) > 0 && strlen(trim($buscarProducto)) >= 1)
                    <div class="absolute z-50 w-full bg-slate-50 border border-slate-200 rounded-xl shadow-xl mt-1 divide-y divide-slate-200/60 max-h-60 overflow-y-auto">
                        @foreach($productosEncontrados as $p)
                            <div 
                                wire:key="prod-search-{{ $p->id }}"
                                wire:mousedown.prevent="agregarProducto({{ $p->id }})" 
                                class="p-3 hover:bg-indigo-100/50 cursor-pointer flex justify-between items-center transition-colors"
                            >
                                <div>
                                    <span class="font-bold text-xs text-slate-900 block">{{ $p->nombre }}</span>
                                    @if($p->marca)
                                        <span class="text-[11px] text-slate-500">({{ $p->marca }})</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-indigo-700 text-sm">$ {{ number_format($p->precio_venta, 2) }}</span>
                                    <span class="text-[11px] block text-slate-500 font-medium">Stock: {{ $p->stock ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('items') <span class="text-red-600 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
        </div>

        <!-- GRID/CATÁLOGO RÁPIDO DE PRODUCTOS PARA HACER CLIC -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-3">
            <div class="flex justify-between items-center border-b border-slate-200/80 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Catálogo Rápido (Haz Clic para Agregar)</h3>
                <span class="text-[11px] font-semibold text-slate-500">{{ count($catalogoProductos) }} disponibles</span>
            </div>

            <!-- Lista tipo tabla -->
            <div class="overflow-x-auto border border-slate-200/60 rounded-xl max-h-64 overflow-y-auto bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-200/80 z-10 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Nombre</th>
                            <th class="px-3 py-2">Modelo</th>
                            <th class="px-3 py-2 text-center">Cantidad (Stock)</th>
                            <th class="px-3 py-2 text-right">Importe</th>
                            <th class="px-3 py-2 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 text-xs">
                        @forelse($catalogoProductos as $prod)
                            <tr 
                                wire:key="cat-prod-{{ $prod->id }}"
                                wire:click="agregarProducto({{ $prod->id }})"
                                class="hover:bg-indigo-50/70 cursor-pointer transition-colors"
                            >
                                <td class="px-3 py-2 font-bold text-slate-800">
                                    {{ $prod->nombre }}
                                    @if($prod->marca)
                                        <span class="text-[10px] text-slate-400 block font-normal">{{ $prod->marca }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-600 font-medium">
                                    {{ $prod->modelo ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold">
                                    @if($prod->stock !== null)
                                        <span class="px-2 py-0.5 rounded text-[11px] {{ $prod->stock > 0 ? 'bg-slate-200/80 text-slate-700' : 'bg-red-100 text-red-700 font-bold' }}">
                                            {{ $prod->stock }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right font-black text-indigo-700">
                                    $ {{ number_format($prod->precio_venta, 2) }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button 
                                        type="button" 
                                        class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2.5 py-1 rounded-md text-[11px] font-bold transition-colors"
                                    >
                                        + Agregar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 text-xs font-medium">
                                    No se encontraron productos coincidentes en el catálogo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla / Lista de Productos Agregados -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Detalle de la Compra</h3>
            <div class="overflow-x-auto border border-slate-200/60 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3 text-center">Precio</th>
                            <th class="px-4 py-3 text-center">Cantidad</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60 text-xs">
                        @forelse($items as $id => $item)
                            <tr wire:key="item-{{ $id }}" class="hover:bg-indigo-100/30 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $item['nombre'] }}</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-medium">$ {{ number_format($item['precio_unitario'], 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <input 
                                        type="number" 
                                        min="1" 
                                        value="{{ $item['cantidad'] }}" 
                                        wire:change="actualizarCantidad({{ $id }}, $event.target.value)"
                                        class="w-16 text-center bg-slate-50 border-slate-300 rounded-lg p-1 text-xs font-bold text-slate-800"
                                    >
                                </td>
                                <td class="px-4 py-3 text-right font-black text-slate-900">$ {{ number_format($item['subtotal'], 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="quitarProducto({{ $id }})" class="text-red-700 hover:text-red-900 bg-red-100/80 hover:bg-red-200 px-2.5 py-1 rounded-md text-[11px] font-bold transition-colors cursor-pointer">
                                        Quitar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    <p class="text-xs font-medium text-slate-500">No has agregado productos a esta venta aún. Haz clic en las tarjetas de arriba o usa el buscador.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Datos del Cliente, Envío y Cobro (1 Columna) -->
    <div class="space-y-6">

        <!-- Formulario del Cliente y Entrega -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">2. Datos del Cliente</h3>

            <!-- Campo con Autocompletado de Cliente -->
            <div class="relative">
                <x-input-label value="Nombre del Cliente *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                
                <x-text-input 
                    type="text" 
                    wire:model.live.debounce.150ms="cliente_nombre" 
                    class="w-full mt-1 bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-xs text-slate-800 rounded-lg" 
                    placeholder="Ej: Juan Pérez" 
                    autocomplete="off"
                />

                <!-- Menú emergente de coincidencias de clientes -->
                @if(!empty($clientesEncontrados) && count($clientesEncontrados) > 0 && strlen(trim($cliente_nombre)) >= 1)
                    <div class="absolute z-50 w-full bg-white border border-slate-200 rounded-xl shadow-xl mt-1 divide-y divide-slate-100 max-h-48 overflow-y-auto">
                        @foreach($clientesEncontrados as $c)
                            <div 
                                wire:key="cliente-search-{{ $c->id }}"
                                wire:mousedown.prevent="seleccionarCliente({{ $c->id }})" 
                                class="p-2.5 hover:bg-indigo-50 cursor-pointer transition-colors flex justify-between items-center"
                            >
                                <div>
                                    <span class="font-bold text-xs text-slate-900 block">
                                        {{ $c->nombre }} {{ $c->apellido }}
                                    </span>
                                    @if($c->telefono)
                                        <span class="text-[11px] text-slate-500">Tel: {{ $c->telefono }}</span>
                                    @endif
                                </div>
                                @if($c->direccion)
                                    <span class="text-[10px] text-slate-400 max-w-[120px] truncate">{{ $c->direccion }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @error('cliente_nombre') <span class="text-red-600 text-xs font-semibold block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Teléfono" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                    <x-text-input type="text" wire:model="cliente_telefono" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="099123456" />
                </div>
                <div>
                    <x-input-label value="Tipo de Entrega" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                    <select wire:model.live="tipo_entrega" class="w-full mt-1 bg-slate-50 border-slate-300/80 rounded-lg shadow-sm text-xs font-semibold text-slate-800 focus:border-indigo-500">
                        <option value="retiro">Retira en Local</option>
                        <option value="flete">Envío / Flete</option>
                    </select>
                </div>
            </div>

            @if($tipo_entrega === 'flete')
                <div class="space-y-3 bg-slate-200/50 p-3.5 rounded-xl border border-slate-200/60">
                    <div>
                        <x-input-label value="Dirección de Envío" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <x-text-input type="text" wire:model="cliente_direccion" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="Calle y número" />
                    </div>
                    <div>
                        <x-input-label value="Costo del Flete ($)" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <x-text-input type="number" step="0.01" wire:model.live="costo_flete" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" />
                    </div>
                </div>
            @endif
        </div>

        <!-- Método de Pago y Resumen Final -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">3. Pago y Confirmación</h3>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Método de Pago" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                    <select wire:model.live="metodo_pago" class="w-full mt-1 bg-slate-50 border-slate-300/80 rounded-lg shadow-sm text-xs font-semibold text-slate-800 focus:border-indigo-500">
                        <option value="Contado">Contado / Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta">Tarjeta de Crédito</option>
                        <option value="Débito">Tarjeta de Débito</option>
                    </select>
                </div>

                @if($metodo_pago === 'Tarjeta')
                    <div>
                        <x-input-label value="Cuotas" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <x-text-input type="number" min="1" wire:model="cuotas" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" />
                    </div>
                @endif
            </div>

            <div>
                <x-input-label value="Descuento ($)" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                <x-text-input type="number" step="0.01" wire:model.live="descuento" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="0.00" />
            </div>

            <!-- Resumen Total -->
            <div class="bg-slate-200/50 p-4 rounded-xl space-y-2 border border-slate-200/60">
                <div class="flex justify-between text-xs text-slate-600 font-medium">
                    <span>Subtotal Productos:</span>
                    <span class="font-bold text-slate-800">$ {{ number_format($this->subtotalProductos, 2) }}</span>
                </div>
                @if($tipo_entrega === 'flete')
                    <div class="flex justify-between text-xs text-slate-600 font-medium">
                        <span>Flete:</span>
                        <span class="font-bold text-slate-800">$ {{ number_format((float)$costo_flete, 2) }}</span>
                    </div>
                @endif
                @if((float)$descuento > 0)
                    <div class="flex justify-between text-xs text-red-700 font-semibold">
                        <span>Descuento:</span>
                        <span>-$ {{ number_format((float)$descuento, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-black text-indigo-900 border-t border-slate-300/80 pt-2.5">
                    <span>TOTAL:</span>
                    <span>$ {{ number_format($this->totalFinal, 2) }}</span>
                </div>
            </div>

            <button 
                wire:click="guardarVenta" 
                wire:loading.attr="disabled"
                class="w-full py-3 bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs rounded-xl shadow-md transition-all cursor-pointer"
            >
                <span wire:loading.remove>Confirmar y Registrar Venta</span>
                <span wire:loading>Procesando Venta...</span>
            </button>
        </div>

    </div>
            <!-- ALERTA DE PRODUCTOS SIN STOCK O STOCK NEGATIVO -->
        @php
            $itemsSinStock = collect($items)->filter(function($item) {
                return $item['stock_actual'] !== null && ($item['stock_actual'] < $item['cantidad']);
            });
        @endphp

        @if($itemsSinStock->count() > 0)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl shadow-xs space-y-2">
                <div class="flex items-center gap-2 text-amber-800 font-bold text-xs">
                    <span class="text-base">⚠️</span>
                    <span>Aviso: Venta por Encargo / Stock Insuficiente</span>
                </div>
                <p class="text-[11px] text-amber-700 leading-relaxed">
                    Los siguientes productos no tienen stock suficiente disponible. La venta se registrará normalmente y el saldo del inventario quedará en negativo:
                </p>
                <ul class="list-disc list-inside text-[11px] font-semibold text-amber-900 space-y-0.5 pl-1">
                    @foreach($itemsSinStock as $itemSin)
                        <li>
                            <span class="font-bold">{{ $itemSin['nombre'] }}</span> 
                            (Stock actual: <span class="text-red-700 font-black">{{ $itemSin['stock_actual'] }}</span> | Solicitado: {{ $itemSin['cantidad'] }})
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>