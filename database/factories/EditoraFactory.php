<?php

namespace Database\Factories;

use App\Models\Editora;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Editora>
 */
class EditoraFactory extends Factory
{
    private const PREFIXOS = ['Editora', 'Edições', 'Livraria e Editora', 'Casa Editorial'];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->randomElement(self::PREFIXOS).' '.fake()->unique()->lastName(),
        ];
    }
}
