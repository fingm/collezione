<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{

    public function index(Request $request)
    {
        return view('productos.index');
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return view('productos.create');
    }

    /**
     * Guarda un producto recién creado en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'       => 'required|string|max:255',
            'marca'        => 'nullable|string|max:255',
            'modelo'       => 'nullable|string|max:255',
            'descripcion'  => 'nullable|string',
            'costo'        => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'garantia'     => 'nullable|string|max:255',
            'medidas'      => 'nullable|string|max:255',
        ]);

        // Aseguramos que si no se envía activo, el checkbox se guarde correctamente
        $validatedData['activo'] = $request->has('activo');

        Producto::create($validatedData);

        $productos = Producto::get();

        return redirect()->route('productos.index')->with('status', 'Producto creado exitosamente.',compact('productos'));
    }

    /**
     * Muestra el formulario para editar un producto.
     */
    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    /**
     * Actualiza el producto en la base de datos.
     */
    public function update(Request $request, Producto $producto)
    {
        $validatedData = $request->validate([
            'nombre'       => 'required|string|max:255',
            'marca'        => 'nullable|string|max:255',
            'modelo'       => 'nullable|string|max:255',
            'descripcion'  => 'nullable|string',
            'costo'        => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'garantia'     => 'nullable|string|max:255',
            'medidas'      => 'nullable|string|max:255',
        ]);

        $validatedData['activo'] = $request->has('activo');

        $producto->update($validatedData);

        return redirect()->route('productos.index')
            ->with('status', 'Producto actualizado exitosamente.');
    }

    /**
     * Aplica el borrado suave (SoftDelete) al producto.
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('status', 'Producto eliminado del catálogo.');
    }

    public function show(Producto $producto){
        return view('productos.show',compact('producto'));
    }

    public function actualizarStock(Request $request, Producto $producto){
    $request->validate([
        'cantidad' => 'required|integer|min:1',
        'operacion' => 'required|in:sumar,restar',
    ]);

    $cantidad = $request->input('cantidad');
    $operacion = $request->input('operacion');

    if ($operacion === 'restar') {
        if ($producto->stock !== null && $producto->stock < $cantidad) {
            return back()->withErrors(['cantidad' => 'No hay suficiente stock disponible para restar.']);
        }
        $producto->decrement('stock', $cantidad);
    } else {
        // Si el stock era null (sin control), lo inicializa en la cantidad sumada
        if ($producto->stock === null) {
            $producto->stock = $cantidad;
            $producto->save();
        } else {
            $producto->increment('stock', $cantidad);
        }
    }

    return back()->with('status', 'Stock actualizado correctamente.');
}

}
