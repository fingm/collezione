<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VentaController extends Controller
{
    /**
     * Muestra el historial general de ventas.
     */
    public function index(Request $request){
        $fecha = $request->input('fecha', Carbon::today()->format('Y-m-d'));

        // Consulta base filtrada por la fecha
        $queryVentas = Venta::whereDate('fecha_compra', $fecha);

        // Ventas paginadas para la tabla
        $ventas = (clone $queryVentas)
            ->latest()
            ->paginate(15)
            ->appends(['fecha' => $fecha]);

        // Totales generales y desglose
        $totalVendido = (clone $queryVentas)->sum('precio_total');
        $cantidadVentas = (clone $queryVentas)->count();

        // Desglose por método de pago
        $totalEfectivo = (clone $queryVentas)->whereIn('metodo_pago', ['Contado', 'Efectivo'])->sum('precio_total');
        $totalTarjeta  = (clone $queryVentas)->whereIn('metodo_pago', ['Tarjeta', 'Crédito'])->sum('precio_total');
        $totalDebito   = (clone $queryVentas)->where('metodo_pago', 'Débito')->sum('precio_total');
        $totalTransfer = (clone $queryVentas)->where('metodo_pago', 'Transferencia')->sum('precio_total');

        return view('ventas.index', compact(
            'ventas', 
            'fecha', 
            'totalVendido', 
            'cantidadVentas',
            'totalEfectivo',
            'totalTarjeta',
            'totalDebito',
            'totalTransfer'
        ));
    }

    /**
     * Muestra la pantalla del punto de venta (componente Livewire).
     */
    public function create()
    {
        return view('ventas.create');
    }

    /**
     * Muestra el detalle/recibo de una venta con sus productos.
     */
    public function show(Venta $venta)
    {
        // Carga ansiosa para optimizar consultas a la base de datos
        $venta->load('detalles.producto');

        return view('ventas.show', compact('venta'));
    }
}