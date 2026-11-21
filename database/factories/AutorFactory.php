<?php

namespace Database\Factories;

use App\Models\Autor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Autor>
 */
class AutorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->firstName().' '.fake()->lastName(),
        ];
    }
}
