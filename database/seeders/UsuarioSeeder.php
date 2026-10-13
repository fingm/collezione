<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Direccion;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verificar o asegurar la existencia de los roles
        $adminRole = Role::firstOrCreate(['id' => 1], ['nombre' => 'admin', 'descripcion' => 'Administrador']);
        $vendedorRole = Role::firstOrCreate(['id' => 2], ['nombre' => 'vendedor', 'descripcion' => 'Vendedor']);
        $clienteRole = Role::firstOrCreate(['id' => 3], ['nombre' => 'cliente', 'descripcion' => 'Cliente']);

        // 2. Crear Usuario Administrador
        $admin = Usuario::updateOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'role_id'        => $adminRole->id,
                'nombre'         => 'Matias',
                'apellido'       => 'Cartro',
                'tipo_documento' => 'CI',
                'documento'      => '45891234',
                'telefono'       => '099111222',
                'password'       => Hash::make('12345678'),
                'activo'         => true,
                'notas'          => 'Administrador principal del sistema',
            ]
        );

        Direccion::updateOrCreate(
            ['usuario_id' => $admin->id, 'alias' => 'Oficina Principal'],
            [
                'direccion'     => 'Av. Roosevelt',
                'numero_puerta' => '1200',
                'apartamento'   => '402',
                'esquina'       => 'Parada 8',
                'barrio'        => 'Cantegril',
                'ciudad'        => 'Maldonado',
                'departamento'  => 'Maldonado',
                'indicaciones'  => 'Torre de oficinas, de 09 a 18 hs',
                'es_principal'  => true,
            ]
        );

        // 3. Crear Usuario Vendedor
        $vendedor = Usuario::updateOrCreate(
            ['email' => 'vendedor@pos.com'],
            [
                'role_id'        => $vendedorRole->id,
                'nombre'         => 'Gonzalo',
                'apellido'       => 'Pérez',
                'tipo_documento' => 'CI',
                'documento'      => '38921104',
                'telefono'       => '098444555',
                'password'       => Hash::make('12345678'),
                'activo'         => true,
                'notas'          => 'Vendedor de turno mañana/tarde',
            ]
        );

        Direccion::updateOrCreate(
            ['usuario_id' => $vendedor->id, 'alias' => 'Casa'],
            [
                'direccion'     => 'Calle Florida',
                'numero_puerta' => '845',
                'esquina'       => 'Sarandí',
                'barrio'        => 'Centro',
                'ciudad'        => 'Maldonado',
                'departamento'  => 'Maldonado',
                'es_principal'  => true,
            ]
        );

        // 4. Crear Clientes de Prueba
        $clientesData = [
            [
                'nombre'         => 'Lucía',
                'apellido'       => 'García',
                'email'          => 'lucia.garcia@gmail.com',
                'documento'      => '41239875',
                'telefono'       => '099333444',
                'tipo_documento' => 'CI',
                'notas'          => 'Cliente preferencial, entrega por las tardes',
                'direcciones'    => [
                    [
                        'alias'         => 'Casa',
                        'direccion'     => 'Calle Los Tilos',
                        'numero_puerta' => '142',
                        'esquina'       => 'Av. Aiguá',
                        'barrio'        => 'Barrio Rivera',
                        'ciudad'        => 'Maldonado',
                        'departamento'  => 'Maldonado',
                        'indicaciones'  => 'Portón negro de reja',
                        'es_principal'  => true,
                    ],
                    [
                        'alias'         => 'Local Comercial',
                        'direccion'     => 'Peatonal Sarandí',
                        'numero_puerta' => '510',
                        'barrio'        => 'Centro',
                        'ciudad'        => 'Maldonado',
                        'departamento'  => 'Maldonado',
                        'es_principal'  => false,
                    ],
                ]
            ],
            [
                'nombre'         => 'Roberto',
                'apellido'       => 'Fernández',
                'email'          => 'roberto.f@hotmail.com',
                'documento'      => '219876540012',
                'telefono'       => '091222333',
                'tipo_documento' => 'RUT',
                'notas'          => 'Solicita siempre factura con RUT',
                'direcciones'    => [
                    [
                        'alias'         => 'Depósito',
                        'direccion'     => 'Av. Antonio Lussich',
                        'numero_puerta' => '2100',
                        'esquina'       => 'Perenzina',
                        'barrio'        => 'La Sonrisa',
                        'ciudad'        => 'Maldonado',
                        'departamento'  => 'Maldonado',
                        'indicaciones'  => 'Entrada por portón lateral de cargas',
                        'es_principal'  => true,
                    ],
                ]
            ],
            [
                'nombre'         => 'Mariana',
                'apellido'       => 'Silva',
                'email'          => 'mariana.silva@outlook.com',
                'documento'      => '50123498',
                'telefono'       => '092555666',
                'tipo_documento' => 'CI',
                'notas'          => 'Atención rápida en mostrador',
                'direcciones'    => [
                    [
                        'alias'         => 'Apartamento',
                        'direccion'     => 'Calle Zelmar Michelini',
                        'numero_puerta' => '620',
                        'apartamento'   => '201',
                        'esquina'       => '18 de Julio',
                        'barrio'        => 'Centro',
                        'ciudad'        => 'Maldonado',
                        'departamento'  => 'Maldonado',
                        'indicaciones'  => 'Timbre 201',
                        'es_principal'  => true,
                    ],
                ]
            ],
        ];

        foreach ($clientesData as $c) {
            $cliente = Usuario::updateOrCreate(
                ['email' => $c['email']],
                [
                    'role_id'        => $clienteRole->id,
                    'nombre'         => $c['nombre'],
                    'apellido'       => $c['apellido'],
                    'tipo_documento' => $c['tipo_documento'],
                    'documento'      => $c['documento'],
                    'telefono'       => $c['telefono'],
                    'password'       => Hash::make('12345678'),
                    'activo'         => true,
                    'notas'          => $c['notas'],
                ]
            );

            foreach ($c['direcciones'] as $dir) {
                Direccion::updateOrCreate(
                    ['usuario_id' => $cliente->id, 'alias' => $dir['alias']],
                    array_merge($dir, ['usuario_id' => $cliente->id])
                );
            }
        }
    }
}