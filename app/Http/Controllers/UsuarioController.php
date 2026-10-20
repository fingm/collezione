<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuarios.index');
    }

    public function show(Usuario $usuario)
    {
        $usuario->load([
            'role', 
            'direcciones', 
            'ventas' => fn($q) => $q->latest()->take(5), // Compras como cliente
            'ventasRealizadas' => fn($q) => $q->latest()->take(5) // Ventas realizadas como vendedor
        ]);

        return view('usuarios.show', compact('usuario'));
    }


    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Datos del Usuario
            'nombre'         => 'required|string|max:255',
            'apellido'       => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:usuarios,email',
            'password'       => 'required|string|min:6',
            'documento'      => 'nullable|string|max:50|unique:usuarios,documento',
            'tipo_documento' => 'required|in:CI,RUT,DNI,PASAPORTE,OTRO',
            'role_id'        => 'required|exists:roles,id',
            'telefono'       => 'nullable|string|max:50',
            'activo'         => 'nullable|boolean',
            'notas'          => 'nullable|string',

            // Datos de la Dirección
            'alias'          => 'nullable|string|max:100',
            'direccion'      => 'nullable|string|max:255',
            'numero_puerta'  => 'nullable|string|max:50',
            'apartamento'    => 'nullable|string|max:50',
            'esquina'        => 'nullable|string|max:255',
            'barrio'         => 'nullable|string|max:255',
            'ciudad'         => 'nullable|string|max:255',
            'departamento'   => 'nullable|string|max:255',
            'indicaciones'   => 'nullable|string',
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.unique'      => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'documento.unique'  => 'Este documento ya está registrado.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // 1. Crear el usuario
            $usuario = Usuario::create([
                'nombre'         => $validated['nombre'],
                'apellido'       => $validated['apellido'],
                'email'          => $validated['email'],
                'password'       => Hash::make($validated['password']),
                'tipo_documento' => $validated['tipo_documento'],
                'documento'      => $validated['documento'] ?? null,
                'role_id'        => $validated['role_id'],
                'telefono'       => $validated['telefono'] ?? null,
                'activo'         => $request->has('activo'),
                'notas'          => $validated['notas'] ?? null,
            ]);

            // 2. Crear la dirección principal si se ingresó la calle/dirección
            if (!empty($validated['direccion'])) {
                $usuario->direcciones()->create([
                    'alias'         => $validated['alias'] ?? 'Principal',
                    'direccion'     => $validated['direccion'],
                    'numero_puerta' => $validated['numero_puerta'] ?? null,
                    'apartamento'   => $validated['apartamento'] ?? null,
                    'esquina'       => $validated['esquina'] ?? null,
                    'barrio'        => $validated['barrio'] ?? null,
                    'ciudad'        => $validated['ciudad'] ?? 'Maldonado',
                    'departamento'  => $validated['departamento'] ?? 'Maldonado',
                    'indicaciones'  => $validated['indicaciones'] ?? null,
                    'es_principal'  => true,
                ]);
            }
        });

        return redirect()->route('usuarios.index')->with('status', 'Usuario registrado con éxito.');
    }

    public function edit(Usuario $usuario)
    {
        $roles = Role::all();
        $usuario->load('direcciones');
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario)
{
    $validated = $request->validate([
        // Datos del Usuario
        'nombre'         => 'required|string|max:255',
        'apellido'       => 'required|string|max:255',
        'email'          => 'required|email|max:255|unique:usuarios,email,' . $usuario->id,
        'password'       => 'nullable|string|min:6',
        'documento'      => 'nullable|string|max:50|unique:usuarios,documento,' . $usuario->id,
        'tipo_documento' => 'required|in:CI,RUT,DNI,PASAPORTE,OTRO',
        'role_id'        => 'required|exists:roles,id',
        'telefono'       => 'nullable|string|max:50',
        'notas'          => 'nullable|string',

        // Datos de Dirección Principal
        'alias'          => 'nullable|string|max:100',
        'direccion'      => 'nullable|string|max:255',
        'numero_puerta'  => 'nullable|string|max:50',
        'apartamento'    => 'nullable|string|max:50',
        'esquina'        => 'nullable|string|max:255',
        'barrio'         => 'nullable|string|max:255',
        'ciudad'         => 'nullable|string|max:255',
        'departamento'   => 'nullable|string|max:255',
        'indicaciones'   => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $usuario, $validated) {
        // 1. Preparar datos de usuario
        $userData = [
            'nombre'         => $validated['nombre'],
            'apellido'       => $validated['apellido'],
            'email'          => $validated['email'],
            'tipo_documento' => $validated['tipo_documento'],
            'documento'      => $validated['documento'] ?? null,
            'role_id'        => $validated['role_id'],
            'telefono'       => $validated['telefono'] ?? null,
            'activo'         => $request->has('activo'),
            'notas'          => $validated['notas'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $usuario->update($userData);

        // 2. Actualizar o Crear Dirección (usando updateOrCreate)
        if (!empty($validated['direccion'])) {
            // Buscamos si ya tiene alguna dirección
            $direccionExistente = $usuario->direcciones()->where('es_principal', true)->first() 
                                  ?? $usuario->direcciones()->first();

            $direccionData = [
                'alias'         => $validated['alias'] ?? 'Principal',
                'direccion'     => $validated['direccion'],
                'numero_puerta' => $validated['numero_puerta'] ?? null,
                'apartamento'   => $validated['apartamento'] ?? null,
                'esquina'       => $validated['esquina'] ?? null,
                'barrio'        => $validated['barrio'] ?? null,
                'ciudad'        => $validated['ciudad'] ?? 'Maldonado',
                'departamento'  => $validated['departamento'] ?? 'Maldonado',
                'indicaciones'  => $validated['indicaciones'] ?? null,
                'es_principal'  => true,
            ];

            if ($direccionExistente) {
                $direccionExistente->update($direccionData);
            } else {
                $usuario->direcciones()->create($direccionData);
            }
        }
    });

    return redirect()->route('usuarios.index')->with('status', 'Usuario y dirección actualizados con éxito.');
    }
}