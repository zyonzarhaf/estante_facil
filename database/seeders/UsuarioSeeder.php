<?php

namespace Database\Seeders;

use App\Enums\Perfil;
use App\Models\Endereco;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * 5 bibliotecários e 300 leitores fictícios, mais duas contas de
 * demonstração. Todos com a senha "senha-secreta".
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::factory()->bibliotecario()->create([
            'nome' => 'Bibliotecária Demonstração',
            'email' => 'bibliotecaria@biblioteca.local',
        ]);
        Usuario::factory()->bibliotecario()->count(4)->create();

        Usuario::factory()->create([
            'nome' => 'Leitor Demonstração',
            'email' => 'leitor@biblioteca.local',
        ]);

        // Cerca de 20% dos leitores moram no mesmo endereço de outro (endereço compartilhado).
        foreach (range(1, 299) as $ignorado) {
            $compartilhado = fake()->boolean(20) ? Endereco::query()->inRandomOrder()->first() : null;

            Usuario::factory()->create([
                'perfil' => Perfil::Leitor,
                ...($compartilhado ? ['endereco_id' => $compartilhado->id] : []),
            ]);
        }
    }
}
