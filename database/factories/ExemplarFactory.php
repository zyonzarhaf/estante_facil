<?php

namespace Database\Factories;

use App\Enums\EstadoConservacao;
use App\Models\Edicao;
use App\Models\Exemplar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exemplar>
 */
class ExemplarFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_tombo' => 'BV-'.fake()->unique()->numerify('######'),
            'edicao_id' => Edicao::factory(),
            'data_aquisicao' => fake()->dateTimeBetween('-5 years', '-6 months'),
            'estado_conservacao' => fake()->randomElement(EstadoConservacao::cases()),
        ];
    }
}
