<?php

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    // Búsqueda de productos
    public $buscarProducto = '';

    // Carrito temporal: array de productos agregados
    public $items = [];

    // Datos del cliente y la transacción
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
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio_unitario' => $producto->precio_venta,
                'cantidad' => 1,
                'subtotal' => $producto->precio_venta,
                'stock_actual' => $producto->stock,
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

                // Descontar stock si la entidad maneja control de stock
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

            $productosEncontrados = Producto::where(function ($q) use ($query) {
                $q->where('nombre', 'like', '%' . $query . '%')
                ->orWhere('marca', 'like', '%' . $query . '%')
                ->orWhere('modelo', 'like', '%' . $query . '%');
            })
            ->take(5)
            ->get();
        }

        return [
            'productosEncontrados' => $productosEncontrados
        ];
    }
};
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- COLUMNA IZQUIERDA: Buscador y Carrito de Productos (2 Columnas) -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Buscador de Productos -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-3">1. Seleccionar Productos</h3>
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.150ms="buscarProducto" 
                    wire:keydown.enter.prevent="agregarPrimerProducto"
                    placeholder="Escribe el nombre, marca o modelo del producto..." 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <!-- Menú desplegable -->
                @if(!empty($productosEncontrados) && count($productosEncontrados) > 0 && strlen(trim($buscarProducto)) >= 1)
                    <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 divide-y divide-gray-100 max-h-60 overflow-y-auto">
                        @foreach($productosEncontrados as $p)
                            <div 
                                wire:key="prod-{{ $p->id }}"
                                wire:mousedown.prevent="agregarProducto({{ $p->id }})" 
                                class="p-3 hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition-colors"
                            >
                                <div>
                                    <span class="font-semibold text-gray-900">{{ $p->nombre }}</span>
                                    @if($p->marca)
                                        <span class="text-xs text-gray-500 ml-2">({{ $p->marca }})</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-indigo-600">$ {{ number_format($p->precio_venta, 2) }}</span>
                                    <span class="text-xs block text-gray-400">Stock: {{ $p->stock ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('items') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Tabla / Lista de Productos Agregados -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-3">Detalle de la Compra</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Producto</th>
                        <th class="px-4 py-2 text-center">Precio</th>
                        <th class="px-4 py-2 text-center">Cantidad</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                        <th class="px-4 py-2 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $id => $item)
                        <tr wire:key="item-{{ $id }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item['nombre'] }}</td>
                            <td class="px-4 py-3 text-sm text-center">$ {{ number_format($item['precio_unitario'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <input 
                                    type="number" 
                                    min="1" 
                                    value="{{ $item['cantidad'] }}" 
                                    wire:change="actualizarCantidad({{ $id }}, $event.target.value)"
                                    class="w-16 text-center border-gray-300 rounded-md p-1 text-sm"
                                >
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">$ {{ number_format($item['subtotal'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <button wire:click="quitarProducto({{ $id }})" class="text-red-600 hover:text-red-800 text-xs font-bold">
                                    Quitar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">
                                No has agregado productos a esta venta aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Datos del Cliente, Envío y Cobro (1 Columna) -->
    <div class="space-y-6">

        <!-- Formulario del Cliente y Entrega -->
        <div class="bg-white p-6 rounded-lg shadow-sm space-y-4">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">2. Datos del Cliente</h3>

            <div>
                <x-input-label value="Nombre del Cliente *" />
                <x-text-input type="text" wire:model="cliente_nombre" class="w-full mt-1" placeholder="Ej: Juan Pérez" />
                @error('cliente_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <x-input-label value="Teléfono" />
                    <x-text-input type="text" wire:model="cliente_telefono" class="w-full mt-1" placeholder="099123456" />
                </div>
                <div>
                    <x-input-label value="Tipo de Entrega" />
                    <select wire:model.live="tipo_entrega" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="retiro">Retira en Local</option>
                        <option value="flete">Envío / Flete</option>
                    </select>
                </div>
            </div>

            @if($tipo_entrega === 'flete')
                <div class="space-y-2 bg-gray-50 p-3 rounded-md">
                    <div>
                        <x-input-label value="Dirección de Envío" />
                        <x-text-input type="text" wire:model="cliente_direccion" class="w-full mt-1" placeholder="Calle y número" />
                    </div>
                    <div>
                        <x-input-label value="Costo del Flete ($)" />
                        <x-text-input type="number" step="0.01" wire:model.live="costo_flete" class="w-full mt-1" />
                    </div>
                </div>
            @endif
        </div>

        <!-- Método de Pago y Resumen Final -->
        <div class="bg-white p-6 rounded-lg shadow-sm space-y-4">
            <h3 class="text-lg font-bold text-gray-800 border-b pb-2">3. Pago y Confirmación</h3>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <x-input-label value="Método de Pago" />
                    <select wire:model.live="metodo_pago" class="w-full mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="Contado">Contado / Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta">Tarjeta de Crédito</option>
                        <option value="Débito">Tarjeta de Débito</option>
                    </select>
                </div>

                @if($metodo_pago === 'Tarjeta')
                    <div>
                        <x-input-label value="Cuotas" />
                        <x-text-input type="number" min="1" wire:model="cuotas" class="w-full mt-1" />
                    </div>
                @endif
            </div>

            <div>
                <x-input-label value="Descuento ($)" />
                <x-text-input type="number" step="0.01" wire:model.live="descuento" class="w-full mt-1" placeholder="0.00" />
            </div>

            <!-- Resumen Total -->
            <div class="bg-indigo-50 p-4 rounded-lg space-y-2 border border-indigo-100">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal Productos:</span>
                    <span>$ {{ number_format($this->subtotalProductos, 2) }}</span>
                </div>
                @if($tipo_entrega === 'flete')
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Flete:</span>
                        <span>$ {{ number_format((float)$costo_flete, 2) }}</span>
                    </div>
                @endif
                @if((float)$descuento > 0)
                    <div class="flex justify-between text-sm text-red-600">
                        <span>Descuento:</span>
                        <span>-$ {{ number_format((float)$descuento, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-xl font-bold text-indigo-900 border-t pt-2 border-indigo-200">
                    <span>TOTAL:</span>
                    <span>$ {{ number_format($this->totalFinal, 2) }}</span>
                </div>
            </div>

            <button 
                wire:click="guardarVenta" 
                wire:loading.attr="disabled"
                class="w-full py-3 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700 shadow-md transition-colors"
            >
                <span wire:loading.remove>Confirmar y Registrar Venta</span>
                <span wire:loading>Procesando Venta...</span>
            </button>
        </div>

    </div>

</div>