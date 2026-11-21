<?php

namespace Database\Factories;

use App\Enums\Perfil;
use App\Enums\SituacaoUsuario;
use App\Models\Bibliotecario;
use App\Models\Endereco;
use App\Models\Leitor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Cria o usuário e a linha da especialização (leitor ou bibliotecário),
 * respeitando a especialização total e exclusiva (R17).
 *
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Senha de todos os usuários fictícios: "senha-secreta". O hash é
     * calculado uma vez só, para o seeder não gastar tempo com bcrypt.
     */
    protected static ?string $senha = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nome = fake()->firstName().' '.fake()->lastName();

        return [
            'nome' => $nome,
            'cpf' => fake()->unique()->cpf(false),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->optional(0.8)->cellphoneNumber(),
            'endereco_id' => Endereco::factory(),
            'senha' => static::$senha ??= Hash::make('senha-secreta'),
            'perfil' => Perfil::Leitor,
            'situacao' => SituacaoUsuario::Ativo,
        ];
    }

    public function bibliotecario(): static
    {
        return $this->state(fn () => ['perfil' => Perfil::Bibliotecario]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Usuario $usuario): void {
            $usuario->forceFill(['data_cadastro' => fake()->dateTimeBetween('-2 years', '-5 months')])->save();

            match ($usuario->perfil) {
                Perfil::Leitor => Leitor::create(['usuario_id' => $usuario->id]),
                Perfil::Bibliotecario => Bibliotecario::create(['usuario_id' => $usuario->id]),
            };
        });
    }
}
