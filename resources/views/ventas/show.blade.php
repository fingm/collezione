<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Comprobante de Venta #{{ $venta->id }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('ventas.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-200/70 hover:bg-slate-200 px-3.5 py-2 rounded-lg transition-colors">
                    &larr; Volver
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Imprimir Comprobante</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 print:p-0 print:max-w-none">
            <div class="bg-slate-100/70 border border-slate-200/60 p-8 rounded-2xl shadow-sm space-y-6 print:bg-white print:border-none print:shadow-none print:p-0">

                <!-- Encabezado del Recibo -->
                <div class="flex justify-between items-start border-b border-slate-200/80 pb-6">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900">COMPROBANTE DE VENTA</h1>
                        <p class="text-xs font-bold text-slate-500 mt-1">N° de Operación: <span class="text-slate-800">#{{ $venta->id }}</span></p>
                        <p class="text-xs font-medium text-slate-500">Fecha: {{ $venta->fecha_compra ? \Carbon\Carbon::parse($venta->fecha_compra)->format('d/m/Y H:i A') : '-' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100/80 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                            {{ $venta->estado }}
                        </span>
                    </div>
                </div>

                <!-- Información del Cliente y Pago -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-200/50 border border-slate-200/60 p-4 rounded-xl text-xs print:bg-gray-50">
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[11px] mb-1.5">Cliente</h4>
                        <p class="font-bold text-slate-900 text-sm">{{ $venta->cliente_nombre }}</p>
                        <p class="text-slate-600 font-medium">Teléfono: <span class="text-slate-800">{{ $venta->cliente_telefono ?? 'No especificado' }}</span></p>
                        <p class="text-slate-600 font-medium">Dirección: <span class="text-slate-800">{{ $venta->cliente_direccion ?? 'No especificada' }}</span></p>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-500 uppercase tracking-wider text-[11px] mb-1.5">Detalles de Pago y Entrega</h4>
                        <p class="text-slate-600 font-medium">
                            Método de Pago: <span class="font-bold text-slate-900">{{ $venta->metodo_pago }}</span> 
                            {{ $venta->cuotas > 1 ? "({$venta->cuotas} cuotas)" : '' }}
                        </p>
                        <p class="text-slate-600 font-medium">
                            Tipo de Entrega: <span class="font-bold text-slate-900">{{ ucfirst($venta->tipo_entrega) }}</span>
                        </p>
                    </div>
                </div>

                <!-- Tabla de Productos Comprados -->
                <div class="overflow-x-auto border border-slate-200/60 rounded-xl print:border-gray-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-200/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider print:bg-gray-100">
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-4 py-3 text-center">Cantidad</th>
                                <th class="px-4 py-3 text-right">Precio Unitario</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60 text-xs print:divide-gray-200">
                            @foreach($venta->detalles as $detalle)
                                <tr class="hover:bg-indigo-100/30 transition-colors print:hover:bg-transparent">
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        {{ $detalle->producto->nombre ?? 'Producto no disponible' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $detalle->cantidad }}</td>
                                    <td class="px-4 py-3 text-right text-slate-600 font-medium">$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-900">$ {{ number_format($detalle->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totales -->
                <div class="border-t border-slate-200/80 pt-4 flex justify-end">
                    <div class="w-72 space-y-2 text-xs bg-slate-200/40 p-4 rounded-xl border border-slate-200/50 print:bg-transparent print:border-none">
                        <div class="flex justify-between text-slate-600 font-medium">
                            <span>Subtotal Productos:</span>
                            <span class="font-bold text-slate-800">$ {{ number_format($venta->subtotal, 2) }}</span>
                        </div>
                        @if($venta->costo_flete > 0)
                            <div class="flex justify-between text-slate-600 font-medium">
                                <span>Costo Flete:</span>
                                <span class="font-bold text-slate-800">$ {{ number_format($venta->costo_flete, 2) }}</span>
                            </div>
                        @endif
                        @if($venta->descuento > 0)
                            <div class="flex justify-between text-red-700 font-semibold">
                                <span>Descuento:</span>
                                <span>-$ {{ number_format($venta->descuento, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-black text-indigo-900 border-t border-slate-300/80 pt-2.5">
                            <span>TOTAL:</span>
                            <span>$ {{ number_format($venta->precio_total, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>