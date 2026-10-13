<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;

class TestingDatosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear 100 Productos falsos
        $this->command->info('Creando productos...');
        $productos = Producto::factory()->count(100)->create();

        // 2. Crear 50 Ventas simuladas en los últimos 30 días
        $this->command->info('Creando ventas de prueba...');
        
        $metodosPago = ['Contado', 'Transferencia', 'Tarjeta', 'Débito'];
        $tiposEntrega = ['retiro', 'flete'];

        for ($i = 0; $i < 50; $i++) {
            $flete = fake()->boolean(30); // 30% con flete
            $costoFlete = $flete ? fake()->randomElement([150, 200, 300, 500]) : 0;
            $descuento = fake()->boolean(20) ? fake()->randomElement([50, 100, 200]) : 0;

            // Fecha aleatoria dentro del último mes
            $fecha = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 12));

            $venta = Venta::create([
                'cliente_nombre'    => fake()->name(),
                'cliente_telefono'  => fake()->phoneNumber(),
                'cliente_direccion' => $flete ? fake()->address() : null,
                'tipo_entrega'      => $flete ? 'flete' : 'retiro',
                'costo_flete'       => $costoFlete,
                'metodo_pago'       => fake()->randomElement($metodosPago),
                'cuotas'            => rand(1, 12),
                'subtotal'          => 0, // Se calcula abajo
                'descuento'         => $descuento,
                'precio_total'      => 0, // Se calcula abajo
                'fecha_compra'      => $fecha,
                'estado'            => 'completada',
                'notas'             => fake()->optional(0.3)->sentence(),
            ]);

            // Agregar entre 1 y 5 productos por venta
            $subtotalVenta = 0;
            $items = $productos->random(rand(1, 5));

            foreach ($items as $prod) {
                $cant = rand(1, 4);
                $subtotalItem = $cant * $prod->precio_venta;
                $subtotalVenta += $subtotalItem;

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $prod->id,
                    'cantidad'        => $cant,
                    'precio_unitario' => $prod->precio_venta,
                    'subtotal'        => $subtotalItem,
                ]);
            }

            // Actualizar totales de la venta
            $venta->update([
                'subtotal'     => $subtotalVenta,
                'precio_total' => ($subtotalVenta + $costoFlete) - $descuento,
            ]);
        }

        $this->command->info('¡Datos de prueba generados con éxito!');
    }
}