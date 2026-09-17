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
                        <tr>
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
                        <span>- $ {{ number_format((float)$descuento, 2) }}</span>
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