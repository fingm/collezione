<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle del Producto: {{ $producto->nombre }}
            </h2>
            <a href="{{ route('productos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Volver al listado
            </a>
        </div>
    </x-slot>

    <!-- Envoltorio Alpine.js para controlar el modal -->
    <div class="py-12" x-data="{ modalOpen: false, operacion: 'sumar' }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('cantidad'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ $errors->first('cantidad') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                
                <!-- Encabezado con estado -->
                <div class="flex justify-between items-center border-b pb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $producto->nombre }}</h3>
                        <p class="text-sm text-gray-500">
                            Marca: {{ $producto->marca ?? 'N/A' }} | Modelo: {{ $producto->modelo ?? 'N/A' }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $producto->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $producto->activo ? 'Activo para Venta' : 'Inactivo' }}
                    </span>
                </div>

                <!-- Detalle de Precios e Inventario -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg items-center">
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-bold">Costo</span>
                        <p class="text-lg font-semibold text-gray-700">$ {{ number_format($producto->costo, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-bold">Precio de Venta</span>
                        <p class="text-xl font-bold text-indigo-600">$ {{ number_format($producto->precio_venta, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase font-bold">Stock Disponible</span>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-lg font-semibold text-gray-700">
                                {{ $producto->stock !== null ? $producto->stock . ' u.' : 'Sin control' }}
                            </p>
                            <!-- Botón para abrir el popup -->
                            <button @click="modalOpen = true" type="button" class="px-2 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold rounded border border-indigo-200">
                                +/- Ajustar Stock
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Datos Adicionales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700">Garantía</h4>
                        <p class="text-gray-600">{{ $producto->garantia ?? 'Sin garantía especificada' }}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700">Medidas</h4>
                        <p class="text-gray-600">{{ $producto->medidas ?? 'Sin medidas especificadas' }}</p>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-gray-700">Descripción</h4>
                    <p class="text-gray-600 mt-1 whitespace-pre-line">{{ $producto->descripcion ?? 'Sin descripción registrada.' }}</p>
                </div>

                <!-- Botones de Acción -->
                <div class="border-t pt-6 flex justify-between items-center">
                    <form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            Eliminar Producto
                        </button>
                    </form>

                    <a href="{{ route('productos.edit', $producto) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Editar Producto
                    </a>
                </div>

            </div>
        </div>

        <!-- POPUP / MODAL (Alpine.js) -->
        <div x-show="modalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-gray-900 bg-opacity-50"
             x-transition>
            <div @click.away="modalOpen = false" class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900">Ajustar Stock de Inventario</h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                </div>

                <form action="{{ route('productos.stock', $producto) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Operación</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" 
                                    @click="operacion = 'sumar'" 
                                    :class="operacion === 'sumar' ? 'bg-green-600 text-white font-bold' : 'bg-gray-100 text-gray-700'" 
                                    class="py-2 text-center text-sm rounded-md border transition-colors">
                                + Agregar Stock
                            </button>
                            <button type="button" 
                                    @click="operacion = 'restar'" 
                                    :class="operacion === 'restar' ? 'bg-red-600 text-white font-bold' : 'bg-gray-100 text-gray-700'" 
                                    class="py-2 text-center text-sm rounded-md border transition-colors">
                                - Quitar Stock
                            </button>
                        </div>
                        <input type="hidden" name="operacion" :value="operacion">
                    </div>

                    <div>
                        <x-input-label for="cantidad" value="Cantidad de Unidades" />
                        <x-text-input id="cantidad" name="cantidad" type="number" min="1" class="mt-1 block w-full" placeholder="Ej: 5" required />
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-4">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <x-primary-button>
                            Confirmar Ajuste
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>