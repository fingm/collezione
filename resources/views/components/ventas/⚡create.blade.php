<?php

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Usuario;
use App\Models\Direccion;
use App\Models\Envio;
use App\Models\PagoVenta;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    // Búsqueda de productos en la barra superior
    public $buscarProducto = '';

    // Carrito temporal: array de productos agregados
    public $items = [];

    // Datos del cliente y la transacción
    public $cliente_id = null;
    public $cliente_nombre = '';
    public $cliente_telefono = '';
    public $cliente_direccion = '';
    public $tipo_entrega = 'retiro'; // 'retiro' o 'flete'
    public $costo_flete = 0;
    public $descuento = 0;
    public $notas = '';

    // Múltiples Métodos de Pago
    public $pagos = [];
    public $nuevo_metodo = 'Efectivo';
    public $nuevo_monto = '';
    public $nueva_referencia = '';

    // Manejo de direcciones y envíos
    public $direccion_id = null;        // ID de la dirección seleccionada si el cliente ya existe
    public $nueva_direccion = false;    // Toggle para forzar la creación de una dirección nueva
    
    // Campos detallados para una nueva dirección
    public $direccion_calle = '';
    public $direccion_numero = '';
    public $direccion_apartamento = '';
    public $direccion_alias  = '';
    public $direccion_esquina = '';
    public $direccion_barrio = '';
    public $direccion_ciudad = 'Maldonado';
    public $direccion_departamento = 'Maldonado';
    public $direccion_indicaciones = '';

    public function mount()
    {
        $this->pagos = [];
    }

    // Agregar producto al carrito temporal
    public function agregarProducto($productoId)
    {
        $producto = Producto::find($productoId);

        if (!$producto) return;

        if (isset($this->items[$productoId])) {
            $this->items[$productoId]['cantidad']++;
            $this->items[$productoId]['subtotal'] = $this->items[$productoId]['cantidad'] * (float) $this->items[$productoId]['precio_unitario'];
        } else {
            $this->items[$productoId] = [
                'id'              => $producto->id,
                'nombre'          => $producto->nombre,
                'precio_unitario' => (float) $producto->precio_venta,
                'cantidad'        => 1,
                'subtotal'        => (float) $producto->precio_venta,
                'stock_actual'    => $producto->stock,
            ];
        }

        $this->buscarProducto = '';
        $this->autoCompletarPagoRestante();
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
            $this->items[$productoId]['subtotal'] = (int) $cantidad * (float) $this->items[$productoId]['precio_unitario'];
        }

        $this->autoCompletarPagoRestante();
    }

    // Quitar producto del carrito
    public function quitarProducto($productoId)
    {
        unset($this->items[$productoId]);
        $this->autoCompletarPagoRestante();
    }

    // Recalcular el pago sugerido si cambian el flete o el descuento
    public function updatedCostoFlete()
    {
        $this->autoCompletarPagoRestante();
    }

    public function updatedDescuento()
    {
        $this->autoCompletarPagoRestante();
    }

    public function updatedTipoEntrega()
    {
        $this->autoCompletarPagoRestante();
    }

    // Propiedades calculadas en tiempo real
    public function getSubtotalProductosProperty()
    {
        return (float) array_sum(array_column($this->items, 'subtotal'));
    }

    public function getTotalFinalProperty()
    {
        $flete = $this->tipo_entrega === 'flete' ? (float) $this->costo_flete : 0;
        return max(0, ($this->subtotalProductos + $flete) - (float) $this->descuento);
    }

    public function getTotalPagadoProperty()
    {
        return (float) array_sum(array_column($this->pagos, 'monto'));
    }

    public function getSaldoPendienteProperty()
    {
        return max(0, round($this->totalFinal - $this->totalPagado, 2));
    }

    public function getVueltoProperty()
    {
        return max(0, round($this->totalPagado - $this->totalFinal, 2));
    }

    // Métodos para gestionar pagos múltiples
    public function agregarPago()
    {
        $monto = (float) $this->nuevo_monto;

        if ($monto <= 0) return;

        $this->pagos[] = [
            'metodo_pago' => $this->nuevo_metodo,
            'monto'       => $monto,
            'referencia'  => $this->nueva_referencia ? trim($this->nueva_referencia) : null,
        ];

        $this->nueva_referencia = '';
        $this->autoCompletarPagoRestante();
    }

    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);
        $this->autoCompletarPagoRestante();
    }

    public function autoCompletarPagoRestante()
    {
        $saldo = $this->saldoPendiente;
        $this->nuevo_monto = $saldo > 0 ? (string) round($saldo, 2) : '';
    }

    // Método público para seleccionar el cliente y autocompletar
    public function seleccionarCliente($id)
    {
        $cliente = Usuario::with('direcciones')->find($id);

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->cliente_nombre = trim($cliente->nombre . ' ' . ($cliente->apellido ?? ''));
            $this->cliente_telefono = $cliente->telefono ?? '';
            
            if ($cliente->direcciones && $cliente->direcciones->count() > 0) {
                $dirPrincipal = $cliente->direcciones->where('es_principal', true)->first() ?? $cliente->direcciones->first();
                $this->direccion_id = $dirPrincipal->id;
                $this->nueva_direccion = false;
            } else {
                $this->direccion_id = null;
                $this->nueva_direccion = true;
            }
        }
    }

    public function updatedClienteNombre()
    {
        $this->cliente_id = null;
        $this->direccion_id = null;
        $this->nueva_direccion = true;
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
            'cliente_nombre'  => 'required|string|max:255',
            'items'           => 'required|array|min:1',
            'pagos'           => 'required|array|min:1',
            'costo_flete'     => 'nullable|numeric|min:0',
            'descuento'       => 'nullable|numeric|min:0',
            'direccion_calle' => $this->tipo_entrega === 'flete' && ($this->nueva_direccion || !$this->cliente_id) ? 'required|string|max:255' : 'nullable',
        ], [
            'items.required'           => 'Debes agregar al menos un producto a la venta.',
            'pagos.required'           => 'Debes agregar al menos un método de pago.',
            'cliente_nombre.required'  => 'El nombre del cliente es obligatorio.',
            'direccion_calle.required' => 'La calle / dirección es obligatoria para el envío.',
        ]);

        if ($this->totalPagado < $this->totalFinal) {
            $this->addError('pagos', 'El monto total ingresado es inferior al total de la venta.');
            return;
        }


        DB::transaction(function () {
            // 1. Garantizar la existencia del usuario cliente si no fue seleccionado
            if (!$this->cliente_id && !empty(trim($this->cliente_nombre))) {
                $partesNombre = explode(' ', trim($this->cliente_nombre), 2);
                
                $nuevoUsuario = Usuario::create([
                    'nombre'   => $partesNombre[0],
                    'apellido' => $partesNombre[1] ?? '',
                    'telefono' => $this->cliente_telefono ?? null,
                    'email'    => 'cliente_' . time() . '_' . rand(100, 999) . '@pos.local',
                    'password' => bcrypt('12345678'),
                    'role_id'  => 2,
                ]);

                $this->cliente_id = $nuevoUsuario->id;
            }

            // 2. Crear la Venta (Cabecera) asociando el vendedor autenticado
            $metodosResumen = collect($this->pagos)->pluck('metodo_pago')->unique()->implode(' + ');

            $venta = Venta::create([
                'cliente_id'        => $this->cliente_id,
                'vendedor_id'       => auth()->id(), // <--- Asigna el ID del usuario logueado
                'cliente_nombre'    => $this->cliente_nombre,
                'cliente_telefono'  => $this->cliente_telefono,
                'cliente_direccion' => $this->cliente_direccion,
                'tipo_entrega'      => $this->tipo_entrega,
                'costo_flete'       => $this->tipo_entrega === 'flete' ? $this->costo_flete : 0,
                'metodo_pago'       => $metodosResumen,
                'subtotal'          => $this->subtotalProductos,
                'descuento'         => $this->descuento,
                'precio_total'      => $this->totalFinal,
                'fecha_compra'      => now(),
                'estado'            => 'completada',
                'notas'             => $this->notas,
            ]);

  

            // 3. Registrar los distintos métodos de pago en 'pago_ventas'
            foreach ($this->pagos as $pago) {
                PagoVenta::create([
                    'venta_id'    => $venta->id,
                    'metodo_pago' => $pago['metodo_pago'],
                    'monto'       => $pago['monto'],
                    'referencia'  => $pago['referencia'] ?? null,
                ]);
            }

            // 4. Procesar Dirección y Envío
            if ($this->tipo_entrega === 'flete') {
                $dirId = $this->direccion_id;

                if ($this->nueva_direccion || !$dirId) {
                    $direccionCreada = Direccion::create([
                        'usuario_id'    => $this->cliente_id,
                        'alias'         => $this->direccion_alias,
                        'direccion'     => $this->direccion_calle,
                        'numero_puerta' => $this->direccion_numero,
                        'apartamento'   => $this->direccion_apartamento,
                        'esquina'       => $this->direccion_esquina,
                        'barrio'        => $this->direccion_barrio,
                        'ciudad'        => $this->direccion_ciudad ?? 'Maldonado',
                        'departamento'  => $this->direccion_departamento ?? 'Maldonado',
                        'indicaciones'  => $this->direccion_indicaciones,
                    ]);
                    $dirId = $direccionCreada->id;
                }

                Envio::create([
                    'venta_id'      => $venta->id,
                    'direccion_id'  => $dirId,
                    'estado'        => 'pendiente',
                    'fecha_pactada' => now()->toDateString(),
                    'notas_envio'   => $this->notas,
                ]);
            }

            // 5. Crear DetalleVenta y actualizar Stock
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

        $clientesEncontrados = [];
        $queryCliente = trim($this->cliente_nombre);

        if ($queryCliente !== '' && !$this->cliente_id) {
            $clientesEncontrados = Usuario::with('direcciones')
                ->where('role_id', 2)
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
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">1. Buscar o Seleccionar Productos</h3>
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

        <!-- GRID/CATÁLOGO RÁPIDO DE PRODUCTOS -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-3">
            <div class="flex justify-between items-center border-b border-slate-200/80 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Catálogo Rápido</h3>
                <span class="text-[11px] font-semibold text-slate-500">{{ count($catalogoProductos) }} disponibles</span>
            </div>

            <div class="overflow-x-auto border border-slate-200/60 rounded-xl max-h-64 overflow-y-auto bg-white">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-200/80 z-10 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Nombre</th>
                            <th class="px-3 py-2">Modelo</th>
                            <th class="px-3 py-2 text-center">Stock</th>
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

        <!-- Tabla de Productos Agregados -->
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
                                    <p class="text-xs font-medium text-slate-500">No has agregado productos a esta venta aún.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Datos del Cliente, Envío y Cobro Multimedio -->
    <div class="space-y-6">

        <!-- Formulario del Cliente y Entrega -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">2. Datos del Cliente y Entrega</h3>

            <div class="relative">
                <x-input-label value="Nombre del Cliente *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                
                <x-text-input 
                    type="text" 
                    wire:model.live.debounce.150ms="cliente_nombre" 
                    class="w-full mt-1 bg-slate-50 border-slate-300/80 focus:border-indigo-500 text-xs text-slate-800 rounded-lg" 
                    placeholder="Ej: Juan Pérez" 
                    autocomplete="off"
                />

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
                <div class="space-y-4 bg-slate-200/50 p-4 rounded-xl border border-slate-200/60 mt-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300/60 pb-2">
                        Detalles del Envío
                    </h4>

                    <div>
                        <x-input-label value="Costo del Flete ($)" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <x-text-input type="number" step="0.01" wire:model.live.debounce.300ms="costo_flete" class="w-full bg-slate-50 border-slate-300/80 text-xs font-bold text-slate-800 rounded-lg" placeholder="0.00" />
                    </div>

                    @if($cliente_id)
                        @php
                            $clienteSel = Usuario::with('direcciones')->find($cliente_id);
                            $direccionesCliente = $clienteSel ? $clienteSel->direcciones : collect();
                        @endphp

                        @if($direccionesCliente->count() > 0 && !$nueva_direccion)
                            <div>
                                <x-input-label value="Seleccionar Dirección Guardada" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1" />
                                <select wire:model.live="direccion_id" class="w-full bg-slate-50 border-slate-300/80 rounded-lg text-xs text-slate-800 focus:border-indigo-500">
                                    @foreach($direccionesCliente as $dir)
                                        <option value="{{ $dir->id }}">
                                            {{ $dir->alias ? '['.$dir->alias.'] ' : '' }}{{ $dir->direccion }} {{ $dir->numero_puerta }} 
                                            {{ $dir->barrio ? '- B° '.$dir->barrio : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="$set('nueva_direccion', true)" class="text-indigo-600 hover:text-indigo-800 text-[11px] font-bold mt-1.5 inline-block">
                                    + Agregar una nueva dirección
                                </button>
                            </div>
                        @else
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-700">Nueva Dirección</span>
                                @if($direccionesCliente->count() > 0)
                                    <button type="button" wire:click="$set('nueva_direccion', false)" class="text-xs text-slate-500 underline font-semibold">
                                        Usar dirección guardada
                                    </button>
                                @endif
                            </div>
                        @endif
                    @endif

                    @if(!$cliente_id || $nueva_direccion || (isset($direccionesCliente) && $direccionesCliente->count() === 0))
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="col-span-2">
                                <x-input-label value="Alias" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                                <x-text-input type="text" wire:model="direccion_alias" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="Casa, Trabajo, etc." />
                            </div>
                            <div class="col-span-2">
                                <x-input-label value="Calle / Vía *" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                                <x-text-input type="text" wire:model="direccion_calle" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="Ej: Av. Gorlero" />
                                @error('direccion_calle') <span class="text-red-600 text-[10px] block mt-0.5">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <x-input-label value="N° Puerta" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                                <x-text-input type="text" wire:model="direccion_numero" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="1234" />
                            </div>
                            <div>
                                <x-input-label value="Apto / Bis" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                                <x-text-input type="text" wire:model="direccion_apartamento" class="w-full mt-1 bg-slate-50 border-slate-300/80 text-xs text-slate-800 rounded-lg" placeholder="Apto 201" />
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Módulo de Cobro Multimedio -->
        <div class="bg-slate-100/70 border border-slate-200/60 p-6 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200/80 pb-3">3. Resumen y Pagos</h3>

            <!-- Descuento Global -->
            <div>
                <x-input-label value="Descuento ($)" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                <x-text-input type="number" step="0.01" wire:model.live.debounce.300ms="descuento" class="w-full bg-slate-50 border-slate-300/80 text-xs font-bold text-slate-800 rounded-lg" placeholder="0.00" />
            </div>

            <!-- Totales -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600 font-medium">
                    <span>Subtotal Productos:</span>
                    <span>$ {{ number_format($this->subtotalProductos, 2) }}</span>
                </div>
                @if($tipo_entrega === 'flete')
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Flete:</span>
                        <span>$ {{ number_format((float)$costo_flete, 2) }}</span>
                    </div>
                @endif
                @if((float)$descuento > 0)
                    <div class="flex justify-between text-emerald-600 font-medium">
                        <span>Descuento:</span>
                        <span>- $ {{ number_format((float)$descuento, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-900 font-black text-sm pt-2 border-t border-slate-200">
                    <span>Total a Pagar:</span>
                    <span class="text-indigo-700">$ {{ number_format($this->totalFinal, 2) }}</span>
                </div>
            </div>

            <!-- Métodos de pago agregados -->
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block">Pagos Registrados</label>
                @forelse($pagos as $index => $pago)
                    <div wire:key="pago-{{ $index }}" class="flex justify-between items-center bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <span class="font-bold text-slate-800">{{ $pago['metodo_pago'] }}</span>
                            @if(!empty($pago['referencia']))
                                <span class="text-[10px] text-slate-400 block">Ref: {{ $pago['referencia'] }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-slate-900">$ {{ number_format($pago['monto'], 2) }}</span>
                            <button type="button" wire:click="quitarPago({{ $index }})" class="text-red-600 hover:text-red-800 font-bold px-1.5 py-0.5 rounded hover:bg-red-50 text-xs">
                                ✕
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-[11px] text-slate-400 italic">No se ha registrado ningún pago aún.</p>
                @endforelse
            </div>

            <!-- Formulario de nuevo pago -->
            <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 space-y-2">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <x-input-label value="Método" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                        <select wire:model="nuevo_metodo" class="w-full mt-1 bg-white border-slate-300 rounded-lg text-xs font-semibold text-slate-800">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                            <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Mercado Pago">Mercado Pago</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Monto *" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                        <x-text-input type="number" step="0.01" wire:model="nuevo_monto" class="w-full mt-1 bg-white border-slate-300 text-xs font-bold text-slate-800 rounded-lg" placeholder="0.00" />
                    </div>
                </div>
                <div>
                    <x-input-label value="N° Referencia / Comprobante (Opcional)" class="text-[10px] font-bold uppercase tracking-wider text-slate-600" />
                    <x-text-input type="text" wire:model="nueva_referencia" class="w-full mt-1 bg-white border-slate-300 text-xs text-slate-800 rounded-lg" placeholder="Ej: #123456" />
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" wire:click="agregarPago" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg text-xs transition-colors">
                        + Agregar Pago
                    </button>
                    @if($this->saldoPendiente > 0)
                        <button type="button" wire:click="autoCompletarPagoRestante" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-3 py-2 rounded-lg text-[11px] whitespace-nowrap transition-colors">
                            Restante
                        </button>
                    @endif
                </div>
            </div>

            <!-- Balances finales (Pendiente / Vuelto) -->
            <div class="space-y-1 text-xs font-bold pt-2 border-t border-slate-200">
                <div class="flex justify-between items-center {{ $this->saldoPendiente > 0 ? 'text-red-600' : 'text-slate-400' }}">
                    <span>Saldo Restante Pendiente:</span>
                    <span>$ {{ number_format($this->saldoPendiente, 2) }}</span>
                </div>
                @if($this->vuelto > 0)
                    <div class="flex justify-between items-center text-emerald-600">
                        <span>Vuelto a Entregar:</span>
                        <span>$ {{ number_format($this->vuelto, 2) }}</span>
                    </div>
                @endif
            </div>

            @error('pagos') <span class="text-red-600 text-xs font-bold block">{{ $message }}</span> @enderror

            <!-- Botón Finalizar -->
            <button 
                type="button" 
                wire:click="guardarVenta" 
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3 rounded-xl shadow-lg hover:shadow-xl transition-all text-sm uppercase tracking-wider text-center"
            >
                Confirmar y Registrar Venta
            </button>
        </div>
    </div>
</div>