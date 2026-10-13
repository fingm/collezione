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
        return view('ventas.index');
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