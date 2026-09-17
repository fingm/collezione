<div>
    <!-- Filtro de Búsqueda Reactivo -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <div class="flex gap-4">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="buscar" 
                placeholder="Buscar por nombre, marca o modelo ..." 
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
            >
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca / Modelo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Venta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($productos as $producto)
                        <tr wire:key="prod-{{ $producto->id }}">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $producto->nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $producto->marca ?? '-' }} {{ $producto->modelo ? "($producto->modelo)" : '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$ {{ number_format($producto->costo, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">$ {{ number_format($producto->precio_venta, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $producto->stock !== null ? $producto->stock : 'Sin control' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $producto->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('productos.show', $producto->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron productos coincidentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $productos->links() }}
            </div>
        </div>
    </div>
</div>