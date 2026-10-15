<?php

namespace App\Http\Controllers;

use App\Models\Direccion;
use Illuminate\Http\Request;

class DireccionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id'    => 'required|exists:usuarios,id',
            'alias'         => 'nullable|string|max:100',
            'direccion'     => 'required|string|max:255',
            'numero_puerta' => 'nullable|string|max:50',
            'apartamento'   => 'nullable|string|max:50',
            'esquina'       => 'nullable|string|max:150',
            'barrio'        => 'nullable|string|max:100',
            'ciudad'        => 'required|string|max:100',
            'departamento'  => 'required|string|max:100',
            'indicaciones'  => 'nullable|string|max:255',
            'es_principal'  => 'nullable|boolean',
        ]);

        // Si la nueva dirección se marca como principal, desmarcamos las demás del usuario
        if (!empty($validated['es_principal'])) {
            Direccion::where('usuario_id', $validated['usuario_id'])->update(['es_principal' => false]);
        }

        Direccion::create($validated);

        return back()->with('status', '¡Dirección agregada con éxito!');
    }

    public function destroy(Direccion $direccion)
    {
        $direccion->delete();

        return back()->with('status', 'Dirección eliminada correctamente.');
    }

    public function update(Request $request, Direccion $direccion)
    {
        $validated = $request->validate([
            'alias'         => 'nullable|string|max:100',
            'direccion'     => 'required|string|max:255',
            'numero_puerta' => 'nullable|string|max:50',
            'apartamento'   => 'nullable|string|max:50',
            'esquina'       => 'nullable|string|max:150',
            'barrio'        => 'nullable|string|max:100',
            'ciudad'        => 'required|string|max:100',
            'departamento'  => 'required|string|max:100',
            'indicaciones'  => 'nullable|string|max:255',
            'es_principal'  => 'nullable|boolean',
        ]);

        $validated['es_principal'] = $request->has('es_principal');

        if ($validated['es_principal']) {
            Direccion::where('usuario_id', $direccion->usuario_id)->update(['es_principal' => false]);
        }

        $direccion->update($validated);

        return back()->with('status', '¡Dirección actualizada correctamente!');
    }
}