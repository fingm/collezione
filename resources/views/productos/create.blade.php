<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form method="POST" action="{{ route('productos.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <x-input-label for="nombre" value="Nombre *" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <!-- Marca -->
                        <div>
                            <x-input-label for="marca" value="Marca" />
                            <x-text-input id="marca" name="marca" type="text" class="mt-1 block w-full" :value="old('marca')" />
                            <x-input-error :messages="$errors->get('marca')" class="mt-2" />
                        </div>

                        <!-- Modelo -->
                        <div>
                            <x-input-label for="modelo" value="Modelo" />
                            <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full" :value="old('modelo')" />
                            <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                        </div>

                        <!-- Stock -->
                        <div>
                            <x-input-label for="stock" value="Stock (Dejar vacío si no controla stock)" />
                            <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full" :value="old('stock')" min="0" />
                            <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                        </div>

                        <!-- Costo -->
                        <div>
                            <x-input-label for="costo" value="Costo *" />
                            <x-text-input id="costo" name="costo" type="number" step="0.01" class="mt-1 block w-full" :value="old('costo')" required />
                            <x-input-error :messages="$errors->get('costo')" class="mt-2" />
                        </div>

                        <!-- Precio Venta -->
                        <div>
                            <x-input-label for="precio_venta" value="Precio Venta *" />
                            <x-text-input id="precio_venta" name="precio_venta" type="number" step="0.01" class="mt-1 block w-full" :value="old('precio_venta')" required />
                            <x-input-error :messages="$errors->get('precio_venta')" class="mt-2" />
                        </div>

                        <!-- Garantía -->
                        <div>
                            <x-input-label for="garantia" value="Garantía" />
                            <x-text-input id="garantia" name="garantia" type="text" class="mt-1 block w-full" :value="old('garantia')" placeholder="Ej: 12 meses" />
                        </div>

                        <!-- Medidas -->
                        <div>
                            <x-input-label for="medidas" value="Medidas" />
                            <x-text-input id="medidas" name="medidas" type="text" class="mt-1 block w-full" :value="old('medidas')" placeholder="Ej: 30x20 cm" />
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <x-input-label for="descripcion" value="Descripción" />
                        <textarea id="descripcion" name="descripcion" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1" rows="3">{{ old('descripcion') }}</textarea>
                    </div>

                    <!-- Estado Activo -->
                    <div class="block">
                        <label for="activo" class="inline-flex items-center">
                            <input id="activo" type="checkbox" name="activo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('activo', true) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600">Producto Activo para Venta</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('productos.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Cancelar</a>
                        <x-primary-button>Guardar Producto</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>