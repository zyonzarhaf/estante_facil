<?php

namespace Database\Factories;

use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Títulos fictícios montados a partir de listas de palavras.
 *
 * @extends Factory<Obra>
 */
class ObraFactory extends Factory
{
    private const ARTIGOS_E_SUBSTANTIVOS = [
        'A Casa', 'O Rio', 'A Noite', 'O Silêncio', 'A Memória', 'O Jardim', 'A Viagem', 'O Segredo',
        'A Cidade', 'O Inverno', 'A Promessa', 'O Farol', 'A Sombra', 'O Retrato', 'A Ilha', 'O Mar',
        'A Estrada', 'O Relógio', 'A Herança', 'O Labirinto', 'A Janela', 'O Sertão', 'A Carta', 'O Eco',
    ];

    private const COMPLEMENTOS = [
        'das Águas', 'do Esquecimento', 'sem Fim', 'de Vidro', 'das Estrelas', 'do Norte', 'de Papel',
        'dos Ventos', 'Perdido', 'de Pedra', 'da Madrugada', 'das Lembranças', 'do Tempo', 'Encantado',
        'de Areia', 'das Marés', 'do Último Verão', 'de Outono', 'dos Ausentes', 'da Serra',
    ];

    private const SUBTITULOS = [
        'uma história de família', 'crônicas de uma cidade', 'contos reunidos', 'um romance',
        'memórias', 'poemas escolhidos', 'ensaios',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->randomElement(self::ARTIGOS_E_SUBSTANTIVOS).' '.fake()->randomElement(self::COMPLEMENTOS),
            'subtitulo' => fake()->optional(0.3)->randomElement(self::SUBTITULOS),
            'idioma_original' => fake()->randomElement(['Português', 'Português', 'Português', 'Inglês', 'Espanhol', 'Francês']),
            'url_acesso' => null,
        ];
    }

    /**
     * Trabalho acadêmico não publicado: sem edição, disponível só online.
     */
    public function trabalhoAcademico(): static
    {
        return $this->state(fn () => [
            'titulo' => 'Estudo sobre '.mb_strtolower(fake()->randomElement(self::ARTIGOS_E_SUBSTANTIVOS)).' '.fake()->randomElement(self::COMPLEMENTOS),
            'subtitulo' => 'Trabalho de Conclusão de Curso',
            'idioma_original' => 'Português',
            'url_acesso' => 'https://repositorio.exemplo.edu.br/tcc/'.fake()->unique()->numberBetween(1000, 9999),
        ]);
    }
}
