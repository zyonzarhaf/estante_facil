<?php

namespace Database\Factories;

use App\Models\Edicao;
use App\Models\Editora;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Edicao>
 */
class EdicaoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isbn' => '978'.fake()->unique()->numerify('##########'),
            'obra_id' => Obra::factory(),
            'editora_id' => Editora::factory(),
            'numero_edicao' => 1,
            'ano_publicacao' => fake()->numberBetween(1950, (int) now()->year),
            'numero_paginas' => fake()->numberBetween(80, 900),
            'idioma' => 'Português',
        ];
    }
}
