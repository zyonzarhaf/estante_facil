<?php

namespace Database\Factories;

use App\Models\Endereco;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Endereco>
 */
class EnderecoFactory extends Factory
{
    private const BAIRROS = [
        'Centro', 'Boa Vista', 'Boa Viagem', 'Casa Forte', 'Graças', 'Espinheiro', 'Madalena',
        'Várzea', 'Torre', 'Pina', 'Imbiribeira', 'Casa Amarela', 'Derby', 'Aflitos', 'Tamarineira',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'logradouro' => fake()->streetName(),
            'numero' => (string) fake()->numberBetween(1, 3000),
            'complemento' => fake()->optional(0.3)->randomElement(['Apto 101', 'Apto 302', 'Casa 2', 'Bloco B', 'Fundos']),
            'bairro' => fake()->randomElement(self::BAIRROS),
            'cidade' => fake()->city(),
            'uf' => fake()->stateAbbr(),
            'cep' => preg_replace('/\D/', '', fake()->postcode()),
        ];
    }
}
