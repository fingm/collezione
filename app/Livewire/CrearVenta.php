<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class CrearVenta extends Component
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
            $this->items[$productoId]['cantidad'] = $cantidad;
            $this->items[$productoId]['subtotal'] = $cantidad * $this->items[$productoId]['precio_unitario'];
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

    public function render()
    {
        $productosEncontrados = [];

        // Buscamos si hay al menos 1 caracter tipeado
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

        return view('livewire.crear-venta', [
            'productosEncontrados' => $productosEncontrados
        ]);
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

}