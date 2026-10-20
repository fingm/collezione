<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class PerfilController extends Controller
{
    public function show()
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        // Cargar las relaciones necesarias para el usuario autenticado
        $usuario->load([
            'role',
            'direcciones',
            'ventas' => fn($q) => $q->latest()->take(10), // Compras del usuario
            'ventasRealizadas' => fn($q) => $q->latest()->take(10) // Ventas si el usuario es Vendedor/Admin
        ]);

        return view('perfil.show', compact('usuario'));
    }

    public function update(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'apellido'       => 'required|string|max:255',
            'tipo_documento' => 'required|string',
            'documento'      => 'nullable|string|max:50',
            'telefono'       => 'nullable|string|max:50',
            'password'       => 'nullable|string|min:8|confirmed',
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'apellido.required'  => 'El apellido es obligatorio.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $datos = [
            'nombre'         => $request->nombre,
            'apellido'       => $request->apellido,
            'tipo_documento' => $request->tipo_documento,
            'documento'      => $request->documento,
            'telefono'       => $request->telefono,
        ];

        // Si ingresó una nueva contraseña, la encriptamos
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('perfil.show')->with('status', 'Perfil actualizado con éxito.');
    }
}