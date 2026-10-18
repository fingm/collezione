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

        // 2. Administrador Único
        $admin = Usuario::updateOrCreate(
            ['email' => 'matias@matias'],
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
                'indicaciones'  => 'Torre de oficinas, horario de 09 a 18 hs',
                'es_principal'  => true,
            ]
        );

        // 3. Crear 5 Vendedores / Vendedoras
        $vendedoresData = [
            [
                'nombre'    => 'Gonzalo',
                'apellido'  => 'Pérez',
                'email'     => 'gonzalo.perez@pos.com',
                'documento' => '38921104',
                'telefono'  => '098444555',
                'notas'     => 'Vendedor de turno mañana',
                'direccion' => [
                    'alias'         => 'Casa',
                    'direccion'     => 'Calle Florida',
                    'numero_puerta' => '845',
                    'esquina'       => 'Sarandí',
                    'barrio'        => 'Centro',
                    'ciudad'        => 'Maldonado',
                    'departamento'  => 'Maldonado',
                ]
            ],
            [
                'nombre'    => 'Mariana',
                'apellido'  => 'Sosa',
                'email'     => 'mariana.sosa@pos.com',
                'documento' => '42109876',
                'telefono'  => '099887766',
                'notas'     => 'Vendedora turno tarde / Encargada de mostrador',
                'direccion' => [
                    'alias'         => 'Casa',
                    'direccion'     => 'Calle San José',
                    'numero_puerta' => '312',
                    'esquina'       => '18 de Julio',
                    'barrio'        => 'Centro',
                    'ciudad'        => 'Maldonado',
                    'departamento'  => 'Maldonado',
                ]
            ],
            [
                'nombre'    => 'Joaquín',
                'apellido'  => 'Rodríguez',
                'email'     => 'joaquin.rodriguez@pos.com',
                'documento' => '49871230',
                'telefono'  => '091234888',
                'notas'     => 'Vendedor de depósito y preventa',
                'direccion' => [
                    'alias'         => 'Casa',
                    'direccion'     => 'Av. Lavalleja',
                    'numero_puerta' => '1020',
                    'esquina'       => 'Ventura Alegre',
                    'barrio'        => 'Maldonado Nuevo',
                    'ciudad'        => 'Maldonado',
                    'departamento'  => 'Maldonado',
                ]
            ],
            [
                'nombre'    => 'Valentina',
                'apellido'  => 'Nunez',
                'email'     => 'valentina.nunez@pos.com',
                'documento' => '51209341',
                'telefono'  => '092110022',
                'notas'     => 'Vendedora de fin de semana',
                'direccion' => [
                    'alias'         => 'Casa',
                    'direccion'     => 'Av. Batlle y Ordóñez',
                    'numero_puerta' => '540',
                    'esquina'       => 'Bergalli',
                    'barrio'        => 'Centro',
                    'ciudad'        => 'Maldonado',
                    'departamento'  => 'Maldonado',
                ]
            ],
            [
                'nombre'    => 'Federico',
                'apellido'  => 'Alvarez',
                'email'     => 'federico.alvarez@pos.com',
                'documento' => '43098125',
                'telefono'  => '098771122',
                'notas'     => 'Vendedor y soporte en envíos',
                'direccion' => [
                    'alias'         => 'Casa',
                    'direccion'     => 'Calle Rincón',
                    'numero_puerta' => '678',
                    'esquina'       => 'Treinta y Tres',
                    'barrio'        => 'Centro',
                    'ciudad'        => 'Maldonado',
                    'departamento'  => 'Maldonado',
                ]
            ],
        ];

        foreach ($vendedoresData as $v) {
            $vendedor = Usuario::updateOrCreate(
                ['email' => $v['email']],
                [
                    'role_id'        => $vendedorRole->id,
                    'nombre'         => $v['nombre'],
                    'apellido'       => $v['apellido'],
                    'tipo_documento' => 'CI',
                    'documento'      => $v['documento'],
                    'telefono'       => $v['telefono'],
                    'password'       => Hash::make('12345678'),
                    'activo'         => true,
                    'notas'          => $v['notas'],
                ]
            );

            Direccion::updateOrCreate(
                ['usuario_id' => $vendedor->id, 'alias' => $v['direccion']['alias']],
                array_merge($v['direccion'], ['usuario_id' => $vendedor->id, 'es_principal' => true])
            );
        }

        // 4. Crear 15 Clientes de Prueba
        $clientesData = [
            [
                'nombre' => 'Lucía', 'apellido' => 'García', 'email' => 'lucia.garcia@gmail.com', 'doc' => '41239875', 'tel' => '099333444', 'tipo' => 'CI', 'notas' => 'Cliente preferencial',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Calle Los Tilos', 'numero_puerta' => '142', 'esquina' => 'Av. Aiguá', 'barrio' => 'Barrio Rivera', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'indicaciones' => 'Portón negro de reja', 'es_principal' => true],
                    ['alias' => 'Local', 'direccion' => 'Peatonal Sarandí', 'numero_puerta' => '510', 'barrio' => 'Centro', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => false]
                ]
            ],
            [
                'nombre' => 'Roberto', 'apellido' => 'Fernández', 'email' => 'roberto.f@hotmail.com', 'doc' => '219876540012', 'tel' => '091222333', 'tipo' => 'RUT', 'notas' => 'Solicita siempre factura con RUT',
                'direcciones' => [
                    ['alias' => 'Depósito', 'direccion' => 'Av. Antonio Lussich', 'numero_puerta' => '2100', 'esquina' => 'Perenzina', 'barrio' => 'La Sonrisa', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'indicaciones' => 'Portón lateral de cargas', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Mariana', 'apellido' => 'Silva', 'email' => 'mariana.silva@outlook.com', 'doc' => '50123498', 'tel' => '092555666', 'tipo' => 'CI', 'notas' => 'Atención rápida en mostrador',
                'direcciones' => [
                    ['alias' => 'Apto', 'direccion' => 'Calle Zelmar Michelini', 'numero_puerta' => '620', 'apartamento' => '201', 'esquina' => '18 de Julio', 'barrio' => 'Centro', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'indicaciones' => 'Timbre 201', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Santiago', 'apellido' => 'Morales', 'email' => 'santiago.m@gmail.com', 'doc' => '47891234', 'tel' => '099881122', 'tipo' => 'CI', 'notas' => 'Entregas solo de tarde',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Av. España', 'numero_puerta' => '1450', 'esquina' => 'Fructuoso Rivera', 'barrio' => 'Las Delicias', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Camila', 'apellido' => 'Benítez', 'email' => 'camila.benitez@yahoo.com', 'doc' => '49012388', 'tel' => '098123999', 'tipo' => 'CI', 'notas' => 'Paga mediante transferencia',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Calle Román Guerra', 'numero_puerta' => '930', 'esquina' => 'Ituzaingó', 'barrio' => 'Centro', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Esteban', 'apellido' => 'Suárez', 'email' => 'esteban.suarez@uy.net', 'doc' => '210987650019', 'tel' => '094332211', 'tipo' => 'RUT', 'notas' => 'Empresa constructora local',
                'direcciones' => [
                    ['alias' => 'Obra Roosevelt', 'direccion' => 'Av. Roosevelt', 'numero_puerta' => 'Parada 11', 'barrio' => 'San Rafael', 'ciudad' => 'Punta del Este', 'departamento' => 'Maldonado', 'indicaciones' => 'Consultar en garita de seguridad', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Florencia', 'apellido' => 'Díaz', 'email' => 'flo.diaz@gmail.com', 'doc' => '52190823', 'tel' => '099001122', 'tipo' => 'CI', 'notas' => 'Cliente frecuente',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Calle Agapito Parabera', 'numero_puerta' => '410', 'esquina' => 'Tammaro', 'barrio' => 'Odizzio', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Matías', 'apellido' => 'Giménez', 'email' => 'matias.gimenez@hotmail.com', 'doc' => '43981200', 'tel' => '098554433', 'tipo' => 'CI', 'notas' => 'Solicita envío agendado',
                'direcciones' => [
                    ['alias' => 'Particular', 'direccion' => 'Calle Bulevar Artigas', 'numero_puerta' => '890', 'esquina' => 'Ledesma', 'barrio' => 'Centro', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Sofia', 'apellido' => 'Pereyra', 'email' => 'sofi.pereyra@gmail.com', 'doc' => '51092834', 'tel' => '091998877', 'tipo' => 'CI', 'notas' => 'Llamar antes de entregar',
                'direcciones' => [
                    ['alias' => 'Apto Sol', 'direccion' => 'Calle San Carlos', 'numero_puerta' => '740', 'apartamento' => '104', 'esquina' => 'Solís', 'barrio' => 'Centro', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Gonzalo', 'apellido' => 'Castro', 'email' => 'gonzalo.castro@outlook.com', 'doc' => '39820192', 'tel' => '099445566', 'tipo' => 'CI', 'notas' => 'Prefiere entregas por la mañana',
                'direcciones' => [
                    ['alias' => 'Oficina', 'direccion' => 'Calle 24 (El Mesana)', 'numero_puerta' => '620', 'esquina' => 'Calle 28', 'barrio' => 'Península', 'ciudad' => 'Punta del Este', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Paula', 'apellido' => 'Rios', 'email' => 'paula.rios@gmail.com', 'doc' => '48901235', 'tel' => '098223344', 'tipo' => 'CI', 'notas' => 'Pagos en efectivo al recibir',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Calle 7 (Los Meros)', 'numero_puerta' => '12', 'esquina' => 'Calle 10', 'barrio' => 'Península', 'ciudad' => 'Punta del Este', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Diego', 'apellido' => 'Noguera', 'email' => 'diego.noguera@adinet.com.uy', 'doc' => '36712098', 'tel' => '099778899', 'tipo' => 'CI', 'notas' => 'Dejar paquete en portería',
                'direcciones' => [
                    ['alias' => 'Edificio Torre', 'direccion' => 'Av. Chiverta', 'numero_puerta' => 'Parada 2', 'apartamento' => '803', 'barrio' => 'Playa Mansa', 'ciudad' => 'Punta del Este', 'departamento' => 'Maldonado', 'indicaciones' => 'Dejar con el portero de turno', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Valeria', 'apellido' => 'Mendoza', 'email' => 'valeria.mendoza@gmail.com', 'doc' => '53091827', 'tel' => '092113355', 'tipo' => 'CI', 'notas' => 'Cliente esporádico',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Calle Los Arrayanes', 'numero_puerta' => '89', 'esquina' => 'Av. Pedragosa Sierra', 'barrio' => 'La Pastora', 'ciudad' => 'Punta del Este', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Andrés', 'apellido' => 'López', 'email' => 'andres.lopez@uy.com', 'doc' => '214567890011', 'tel' => '098900111', 'tipo' => 'RUT', 'notas' => 'Flete comercial pesado',
                'direcciones' => [
                    ['alias' => 'Local Comercial', 'direccion' => 'Av. Camilo De León', 'numero_puerta' => '305', 'esquina' => 'Batlle y Ordóñez', 'barrio' => 'Cerro Pelado', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
                ]
            ],
            [
                'nombre' => 'Agustina', 'apellido' => 'Peralta', 'email' => 'agustina.peralta@gmail.com', 'doc' => '49821093', 'tel' => '091443322', 'tipo' => 'CI', 'notas' => 'Verificar teléfono antes de salir',
                'direcciones' => [
                    ['alias' => 'Casa', 'direccion' => 'Calle Salt Lake', 'numero_puerta' => '120', 'esquina' => 'Av. Italia', 'barrio' => 'Cantegril', 'ciudad' => 'Maldonado', 'departamento' => 'Maldonado', 'es_principal' => true]
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
                    'tipo_documento' => $c['tipo'],
                    'documento'      => $c['doc'],
                    'telefono'       => $c['tel'],
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