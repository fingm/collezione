<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Direccion;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Envio;
use Carbon\Carbon;

class TestingDatosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando la generación de ventas y envíos de prueba...');

        // 1. Obtener productos activos y usuarios con rol de cliente
        $productos = Producto::where('activo', true)->get();
        $clientes  = Usuario::where('role_id', 3)->get(); // Role 3 = Clientes

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos en la base de datos. Ejecuta primero ProductoSeeder.');
            return;
        }

        $metodosPago = ['Contado', 'Transferencia', 'Tarjeta', 'Débito'];
        $estadosEnvio = ['pendiente', 'en_camino', 'entregado'];

        // Obtener un usuario por defecto si no hay cliente asignado (para evitar fallos de Foreign Key en Direccion)
        $usuarioFallback = Usuario::first();

        // 2. Crear 50 ventas distribuidas entre el mes actual y el mes pasado
        for ($i = 0; $i < 50; $i++) {

            // 50% de probabilidad para el mes actual y 50% para el mes pasado
            if (rand(0, 1) === 1) {
                $fecha = Carbon::now()->subDays(rand(0, max(Carbon::now()->day - 1, 1)))->subHours(rand(0, 12));
            } else {
                $fecha = Carbon::now()->subMonth()->startOfMonth()->addDays(rand(0, 27))->subHours(rand(0, 12));
            }

            // Seleccionar cliente registrado o dejar en nulo (Consumidor Final)
            $cliente = $clientes->isNotEmpty() && fake()->boolean(70) ? $clientes->random() : null;
            $clienteNombre = $cliente ? trim($cliente->nombre . ' ' . ($cliente->apellido ?? '')) : fake()->name();
            $clienteTel    = $cliente->telefono ?? fake()->phoneNumber();

            $requiereEnvio = fake()->boolean(50); // 50% de las ventas llevan envío/flete
            $costoFlete    = $requiereEnvio ? fake()->randomElement([150, 200, 300, 500]) : 0;
            $descuento     = fake()->boolean(20) ? fake()->randomElement([50, 100, 200]) : 0;

            // Crear la cabecera de la Venta
            $venta = Venta::create([
                'cliente_id'        => $cliente?->id,
                'cliente_nombre'    => $clienteNombre,
                'cliente_telefono'  => $clienteTel,
                'cliente_direccion' => null,
                'tipo_entrega'      => $requiereEnvio ? 'flete' : 'retiro',
                'costo_flete'       => $costoFlete,
                'metodo_pago'       => fake()->randomElement($metodosPago),
                'cuotas'            => rand(1, 12),
                'subtotal'          => 0,
                'descuento'         => $descuento,
                'precio_total'      => 0,
                'fecha_compra'      => $fecha,
                'estado'            => 'completada',
                'notas'             => fake()->optional(0.2)->sentence(),
            ]);

            // 3. Agregar entre 1 y 4 productos por venta
            $subtotalVenta = 0;
            $items = $productos->random(rand(1, min(4, $productos->count())));

            foreach ($items as $prod) {
                $cant = rand(1, 3);
                $subtotalItem = $cant * $prod->precio_venta; // <-- Corrección de la sintaxis
                $subtotalVenta += $subtotalItem;

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $prod->id,
                    'cantidad'        => $cant,
                    'precio_unitario' => $prod->precio_venta,
                    'subtotal'        => $subtotalItem,
                ]);
            }

            // Actualizar los totales calculados de la venta
            $venta->update([
                'subtotal'     => $subtotalVenta,
                'precio_total' => ($subtotalVenta + $costoFlete) - $descuento,
            ]);

            // 4. Si requiere envío, vincular con la tabla de Direcciones y crear el registro en Envio
            if ($requiereEnvio) {
                if ($cliente && $cliente->direcciones->isNotEmpty()) {
                    $direccionId = $cliente->direcciones->random()->id;
                } else {
                    $nuevaDir = Direccion::create([
                        'usuario_id'    => $cliente?->id ?? $usuarioFallback?->id ?? 1,
                        'alias'         => 'Envío Venta #' . $venta->id,
                        'direccion'     => fake()->streetName(),
                        'numero_puerta' => fake()->buildingNumber(),
                        'apartamento'   => fake()->optional(0.3)->numerify('Apto ###'),
                        'esquina'       => 'esq. ' . fake()->streetName(),
                        'barrio'        => fake()->randomElement(['Centro', 'Barrio Rivera', 'Cantegril', 'La Sonrisa', 'Maldonado Nuevo']),
                        'ciudad'        => 'Maldonado',
                        'departamento'  => 'Maldonado',
                        'indicaciones'  => fake()->optional(0.4)->sentence(),
                    ]);
                    $direccionId = $nuevaDir->id;
                }

                $estado = fake()->randomElement($estadosEnvio);
                
                if ($fecha->isPast() && !$fecha->isCurrentMonth()) {
                    $estado = fake()->boolean(90) ? 'entregado' : 'pendiente';
                }

                $fechaEntrega = ($estado === 'entregado') 
                    ? (clone $fecha)->addHours(rand(2, 48)) 
                    : null;

                Envio::create([
                    'venta_id'            => $venta->id,
                    'direccion_id'        => $direccionId,
                    'estado'              => $estado,
                    'fecha_pactada'       => $fecha->toDateString(),
                    'fecha_entrega'       => $fechaEntrega,
                    'cadete_o_transporte' => $estado === 'entregado' ? fake()->randomElement(['Cadete Juan', 'DAC', 'De Punta', 'Fletero Pedro']) : null,
                    'notas_envio'         => $venta->notas,
                ]);
            }
        }

        $this->command->info('¡50 ventas y sus envíos se generaron exitosamente para el mes actual y el anterior!');
    }
}