<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    public const NOMES = [
        'Romance', 'Conto', 'Poesia', 'Crônica', 'Teatro', 'Ficção científica', 'Fantasia', 'Suspense',
        'Biografia', 'História', 'Filosofia', 'Ciências', 'Tecnologia', 'Infantojuvenil', 'Trabalho acadêmico',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->randomElement(self::NOMES),
        ];
    }
}
