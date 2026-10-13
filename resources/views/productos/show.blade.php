<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Detalle del Producto: {{ $producto->nombre }}
            </h2>
            <a href="{{ route('productos.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-200/70 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                &larr; Volver al listado
            </a>
        </div>
    </x-slot>

    <!-- Envoltorio Alpine.js para controlar modales de stock y eliminación -->
    <div class="py-12" x-data="{ modalStockOpen: false, modalDeleteOpen: false, operacion: 'sumar' }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            
            @if (session('status'))
                <div class="bg-emerald-100/80 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('cantidad'))
                <div class="bg-red-100/80 border border-red-300 text-red-800 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ $errors->first('cantidad') }}
                </div>
            @endif

            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6 space-y-6">
                
                <!-- Encabezado con estado -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900">{{ $producto->nombre }}</h3>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5">
                            Marca: <span class="text-slate-700">{{ $producto->marca ?? 'N/A' }}</span> | Modelo: <span class="text-slate-700">{{ $producto->modelo ?? 'N/A' }}</span>
                        </p>
                    </div>
                    <div>
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $producto->activo ? 'bg-emerald-100/80 text-emerald-800 border border-emerald-200' : 'bg-red-100/80 text-red-800 border border-red-200' }}">
                            {{ $producto->activo ? 'Activo para Venta' : 'Inactivo' }}
                        </span>
                    </div>
                </div>

                <!-- Detalle de Precios e Inventario -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-200/50 border border-slate-200/60 p-4 rounded-xl items-center">
                    <div>
                        <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider">Costo</span>
                        <p class="text-lg font-bold text-slate-700">$ {{ number_format($producto->costo, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider">Precio de Venta</span>
                        <p class="text-xl font-black text-indigo-700">$ {{ number_format($producto->precio_venta, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider">Stock Disponible</span>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-lg font-bold text-slate-800">
                                {{ $producto->stock !== null ? $producto->stock . ' u.' : 'Sin control' }}
                            </p>
                            <!-- Botón para abrir el popup de stock -->
                            <button @click="modalStockOpen = true" type="button" class="px-2.5 py-1 bg-indigo-100/80 hover:bg-indigo-200 text-indigo-800 text-xs font-bold rounded-lg border border-indigo-200 transition-colors cursor-pointer">
                                +/- Ajustar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Datos Adicionales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200/50">
                        <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider mb-1">Garantía</h4>
                        <p class="text-slate-600 font-medium">{{ $producto->garantia ?? 'Sin garantía especificada' }}</p>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200/50">
                        <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider mb-1">Medidas</h4>
                        <p class="text-slate-600 font-medium">{{ $producto->medidas ?? 'Sin medidas especificadas' }}</p>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="border-t border-slate-200/80 pt-4">
                    <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider mb-1">Descripción</h4>
                    <p class="text-slate-600 text-sm whitespace-pre-line leading-relaxed">{{ $producto->descripcion ?? 'Sin descripción registrada.' }}</p>
                </div>

                <!-- Botones de Acción -->
                <div class="border-t border-slate-200/80 pt-6 flex justify-between items-center">
                    <!-- Botón para abrir Modal de Confirmación de Eliminación -->
                    <button type="button" @click="modalDeleteOpen = true" class="px-4 py-2 bg-red-100/80 hover:bg-red-200 text-red-800 text-xs font-bold rounded-lg border border-red-200 transition-colors cursor-pointer">
                        Eliminar Producto
                    </button>

                    <a href="{{ route('productos.edit', $producto) }}" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold rounded-lg transition-colors">
                        Editar Producto
                    </a>
                </div>

            </div>
        </div>

        <!-- POPUP / MODAL: AJUSTAR STOCK -->
        <div x-show="modalStockOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
             x-transition>
            <div @click.away="modalStockOpen = false" class="bg-slate-50 border border-slate-200 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                <div class="flex justify-between items-center border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Ajustar Stock de Inventario</h3>
                    <button @click="modalStockOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('productos.stock', $producto) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Tipo de Operación</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" 
                                    @click="operacion = 'sumar'" 
                                    :class="operacion === 'sumar' ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-sm' : 'bg-slate-200/80 text-slate-700 border-slate-300/80 hover:bg-slate-300/60'" 
                                    class="py-2 text-center text-xs rounded-lg border transition-all cursor-pointer">
                                + Agregar Stock
                            </button>
                            <button type="button" 
                                    @click="operacion = 'restar'" 
                                    :class="operacion === 'restar' ? 'bg-red-600 text-white font-bold border-red-600 shadow-sm' : 'bg-slate-200/80 text-slate-700 border-slate-300/80 hover:bg-slate-300/60'" 
                                    class="py-2 text-center text-xs rounded-lg border transition-all cursor-pointer">
                                - Quitar Stock
                            </button>
                        </div>
                        <input type="hidden" name="operacion" :value="operacion">
                    </div>

                    <div>
                        <x-input-label for="cantidad" value="Cantidad de Unidades" class="text-xs font-bold text-slate-600" />
                        <x-text-input id="cantidad" name="cantidad" type="number" min="1" class="mt-1 block w-full bg-white border-slate-300 rounded-lg text-sm" placeholder="Ej: 5" required />
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                        <button type="button" @click="modalStockOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300/80 text-slate-700 text-xs font-bold rounded-lg transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <x-primary-button class="bg-indigo-700 hover:bg-indigo-800 text-xs">
                            Confirmar Ajuste
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- POPUP / MODAL: CONFIRMAR ELIMINACIÓN -->
        <div x-show="modalDeleteOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
             x-transition>
            <div @click.away="modalDeleteOpen = false" class="bg-slate-50 border border-slate-200 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
                
                <div class="flex items-center gap-3 text-red-600 border-b border-slate-200 pb-3">
                    <div class="p-2 bg-red-100 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">¿Eliminar Producto?</h3>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed">
                    ¿Estás seguro de que deseas eliminar permanentemente <strong class="text-slate-900">{{ $producto->nombre }}</strong>? Esta acción no se puede deshacer.
                </p>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" @click="modalDeleteOpen = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300/80 text-slate-700 text-xs font-bold rounded-lg transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    
                    <form action="{{ route('productos.destroy', $producto) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer">
                            Sí, Eliminar
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>