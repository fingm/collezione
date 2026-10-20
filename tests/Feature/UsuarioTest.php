<?php

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Asignamos directamente a $this para acceder con $this->roleCliente en los tests
    $this->roleAdmin = Role::firstOrCreate(['nombre' => 'Admin']);
    $this->roleCliente = Role::firstOrCreate(['nombre' => 'Cliente']);

    $admin = Usuario::create([
        'nombre'         => 'Admin',
        'apellido'       => 'Sistema',
        'email'          => 'admin_' . rand(100, 999) . '@pos.local',
        'password'       => bcrypt('password123'),
        'tipo_documento' => 'CI',
        'role_id'        => $this->roleAdmin->id,
        'activo'         => true,
    ]);

    $this->actingAs($admin);
});

it('muestra el formulario de creación con los roles disponibles', function () {
    $response = $this->get(route('usuarios.create'));

    $response->assertStatus(200);
    $response->assertViewIs('usuarios.create');
    $response->assertViewHas('roles');
});

it('valida que nombre, apellido, email, tipo de documento y rol sean obligatorios', function () {
    $response = $this->post(route('usuarios.store'), []);

    $response->assertSessionHasErrors([
        'nombre',
        'apellido',
        'email',
        'password',
        'tipo_documento',
        'role_id',
    ]);
});

it('crea un usuario correctamente sin direccion', function () {
    $datos = [
        'nombre'         => 'Matias',
        'apellido'       => 'Cartro',
        'email'          => 'matias@pos.local',
        'password'       => '123456',
        'tipo_documento' => 'CI',
        'documento'      => '12345678',
        'role_id'        => $this->roleCliente->id,
        'telefono'       => '099123456',
        'activo'         => '1',
    ];

    $response = $this->post(route('usuarios.store'), $datos);

    $response->assertRedirect(route('usuarios.index'));
    $response->assertSessionHas('status', 'Usuario registrado con éxito.');

    $this->assertDatabaseHas('usuarios', [
        'email'     => 'matias@pos.local',
        'nombre'    => 'Matias',
        'apellido'  => 'Cartro',
        'documento' => '12345678',
        'role_id'   => $this->roleCliente->id,
    ]);
});

it('crea un usuario junto con su direccion principal si se proporciona la calle', function () {
    $datos = [
        'nombre'         => 'Juan',
        'apellido'       => 'Pérez',
        'email'          => 'juan@pos.local',
        'password'       => '123456',
        'tipo_documento' => 'CI',
        'role_id'        => $this->roleCliente->id,
        'alias'          => 'Casa',
        'direccion'      => 'Av. Gorlero',
        'numero_puerta'  => '1020',
        'ciudad'         => 'Maldonado',
        'departamento'   => 'Maldonado',
    ];

    $response = $this->post(route('usuarios.store'), $datos);

    $response->assertRedirect(route('usuarios.index'));

    $usuarioCreado = Usuario::where('email', 'juan@pos.local')->first();
    expect($usuarioCreado)->not->toBeNull();

    $this->assertDatabaseHas('direcciones', [
        'usuario_id'    => $usuarioCreado->id,
        'direccion'     => 'Av. Gorlero',
        'numero_puerta' => '1020',
        'alias'         => 'Casa',
        'es_principal'  => true,
    ]);
});

it('actualiza un usuario y su direccion principal correctamente', function () {
    $usuario = Usuario::create([
        'nombre'         => 'Pedro',
        'apellido'       => 'Gómez',
        'email'          => 'pedro@pos.local',
        'password'       => bcrypt('123456'),
        'tipo_documento' => 'CI',
        'role_id'        => $this->roleCliente->id,
    ]);

    $usuario->direcciones()->create([
        'alias'        => 'Principal',
        'direccion'    => 'Calle Antigua 123',
        'es_principal' => true,
    ]);

    $datosActualizados = [
        'nombre'         => 'Pedro Luis',
        'apellido'       => 'Gómez',
        'email'          => 'pedro@pos.local',
        'tipo_documento' => 'CI',
        'role_id'        => $this->roleCliente->id,
        'direccion'      => 'Calle Nueva 456',
        'numero_puerta'  => '789',
    ];

    $response = $this->put(route('usuarios.update', $usuario->id), $datosActualizados);

    $response->assertRedirect(route('usuarios.index'));
    $response->assertSessionHas('status', 'Usuario y dirección actualizados con éxito.');

    $this->assertDatabaseHas('usuarios', [
        'id'     => $usuario->id,
        'nombre' => 'Pedro Luis',
    ]);

    $this->assertDatabaseHas('direcciones', [
        'usuario_id'    => $usuario->id,
        'direccion'     => 'Calle Nueva 456',
        'numero_puerta' => '789',
    ]);
});