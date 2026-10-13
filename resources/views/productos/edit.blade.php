<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Editar Producto: ') . $producto->nombre }}
            </h2>
            <a href="{{ route('productos.show', $producto) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-200/70 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                &larr; Volver al detalle
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-slate-100/70 border border-slate-200/60 overflow-hidden shadow-sm sm:rounded-xl p-6">
                
                <form method="POST" action="{{ route('productos.update', $producto) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <x-input-label for="nombre" value="Nombre *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('nombre', $producto->nombre)" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <!-- Marca -->
                        <div>
                            <x-input-label for="marca" value="Marca" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="marca" name="marca" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('marca', $producto->marca)" />
                        </div>

                        <!-- Modelo -->
                        <div>
                            <x-input-label for="modelo" value="Modelo" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('modelo', $producto->modelo)" />
                        </div>

                        <!-- Stock -->
                        <div>
                            <x-input-label for="stock" value="Stock" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('stock', $producto->stock)" min="0" />
                        </div>

                        <!-- Costo -->
                        <div>
                            <x-input-label for="costo" value="Costo ($) *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="costo" name="costo" type="number" step="0.01" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('costo', $producto->costo)" required />
                        </div>

                        <!-- Precio Venta -->
                        <div>
                            <x-input-label for="precio_venta" value="Precio Venta ($) *" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="precio_venta" name="precio_venta" type="number" step="0.01" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('precio_venta', $producto->precio_venta)" required />
                        </div>

                        <!-- Garantía -->
                        <div>
                            <x-input-label for="garantia" value="Garantía" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="garantia" name="garantia" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('garantia', $producto->garantia)" />
                        </div>

                        <!-- Medidas -->
                        <div>
                            <x-input-label for="medidas" value="Medidas" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                            <x-text-input id="medidas" name="medidas" type="text" class="mt-1 block w-full bg-slate-50 border-slate-300/80 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg text-sm" :value="old('medidas', $producto->medidas)" />
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <x-input-label for="descripcion" value="Descripción" class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1" />
                        <textarea id="descripcion" name="descripcion" class="border-slate-300/80 bg-slate-50 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 text-slate-800 rounded-lg shadow-sm w-full mt-1 text-sm" rows="3">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </div>

                    <!-- Estado Activo -->
                    <div class="bg-slate-200/50 border border-slate-200/60 p-4 rounded-xl">
                        <label for="activo" class="inline-flex items-center cursor-pointer">
                            <input id="activo" type="checkbox" name="activo" value="1" class="rounded border-slate-300 text-indigo-700 shadow-sm focus:ring-indigo-500 w-4 h-4" {{ old('activo', $producto->activo) ? 'checked' : '' }}>
                            <span class="ml-2.5 text-xs font-bold uppercase tracking-wider text-slate-700">Producto Activo para Venta</span>
                        </label>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="border-t border-slate-200/80 pt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('productos.show', $producto) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300/80 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <x-primary-button class="bg-indigo-700 hover:bg-indigo-800 text-xs font-bold px-5 py-2.5 rounded-lg">
                            Guardar Cambios
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>