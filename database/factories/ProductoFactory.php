<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costo = $this->faker->randomFloat(2, 100, 5000);
        
        return [
            'nombre'       => ucfirst($this->faker->words(3, true)),
            'marca'        => $this->faker->company(),
            'modelo'       => strtoupper($this->faker->bothify('MOD-???-###')),
            'costo'        => $costo,
            'precio_venta' => round($costo * $this->faker->randomFloat(2, 1.2, 1.8), 2),
            'stock'        => $this->faker->numberBetween(0, 100),
            'activo'       => $this->faker->boolean(90), // 90% probabilidad de estar activo
        ];
    }
}
