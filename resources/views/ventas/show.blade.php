<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Comprobante de Venta #{{ $venta->id }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('ventas.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-300">
                    &larr; Volver
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                    Imprimir Comprobante
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-lg shadow-sm space-y-6">

                <!-- Encabezado del Recibo -->
                <div class="flex justify-between items-start border-b pb-6">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">COMPROBANTE DE VENTA</h1>
                        <p class="text-sm text-gray-500">N° de Operación: #{{ $venta->id }}</p>
                        <p class="text-sm text-gray-500">Fecha: {{ $venta->fecha_compra->format('d/m/Y H:i A') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-sm font-bold rounded-full bg-green-100 text-green-800">
                            {{ strtoupper($venta->estado) }}
                        </span>
                    </div>
                </div>

                <!-- Información del Cliente y Pago -->
                <div class="grid grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg text-sm">
                    <div>
                        <h4 class="font-bold text-gray-700 uppercase mb-1">Cliente</h4>
                        <p class="font-semibold text-gray-900">{{ $venta->cliente_nombre }}</p>
                        <p class="text-gray-600">Tel: {{ $venta->cliente_telefono ?? 'No especificado' }}</p>
                        <p class="text-gray-600">Dirección: {{ $venta->cliente_direccion ?? 'No especificada' }}</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-700 uppercase mb-1">Detalles de Pago y Entrega</h4>
                        <p><span class="text-gray-600">Método de Pago:</span> <span class="font-semibold">{{ $venta->metodo_pago }}</span> {{ $venta->cuotas > 1 ? "({$venta->cuotas} cuotas)" : '' }}</p>
                        <p><span class="text-gray-600">Tipo de Entrega:</span> <span class="font-semibold">{{ ucfirst($venta->tipo_entrega) }}</span></p>
                    </div>
                </div>

                <!-- Tabla de Productos Comprados -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-2 text-left">Producto</th>
                            <th class="px-4 py-2 text-center">Cantidad</th>
                            <th class="px-4 py-2 text-right">Precio Unitario</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($venta->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                    {{ $detalle->producto->nombre ?? 'Producto eliminado' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $detalle->cantidad }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">$ {{ number_format($detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totales -->
                <div class="border-t pt-4 flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal Productos:</span>
                            <span>$ {{ number_format($venta->subtotal, 2) }}</span>
                        </div>
                        @if($venta->costo_flete > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>Costo Flete:</span>
                                <span>$ {{ number_format($venta->costo_flete, 2) }}</span>
                            </div>
                        @endif
                        @if($venta->descuento > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Descuento:</span>
                                <span>- $ {{ number_format($venta->descuento, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold text-indigo-900 border-t pt-2">
                            <span>TOTAL:</span>
                            <span>$ {{ number_format($venta->precio_total, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>