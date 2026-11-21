<?php

namespace App\Services;

use App\Enums\Perfil;
use App\Enums\SituacaoUsuario;
use App\Events\LeitorDesbloqueado;
use App\Models\Endereco;
use App\Models\Leitor;
use App\Models\Usuario;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * RF14: cadastro de leitores pelo bibliotecário. Usuário, Leitor e Endereço
 * são gravados juntos (especialização total, R17), endereços idênticos são
 * compartilhados e endereços sem usuário são removidos.
 */
class ServicoDeLeitor
{
    private const CAMPOS_DO_ENDERECO = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'cep'];

    private const CAMPOS_DO_USUARIO = ['nome', 'cpf', 'email', 'telefone'];

    /**
     * @param  array<string, mixed>  $dados
     */
    public function cadastrar(array $dados): Leitor
    {
        return DB::transaction(function () use ($dados): Leitor {
            $endereco = $this->enderecoPara($dados);

            $usuario = Usuario::create([
                ...Arr::only($dados, self::CAMPOS_DO_USUARIO),
                'senha' => $dados['senha'],
                'endereco_id' => $endereco->id,
                'perfil' => Perfil::Leitor,
                'situacao' => SituacaoUsuario::Ativo,
            ]);

            return Leitor::create(['usuario_id' => $usuario->id]);
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Leitor $leitor, array $dados): void
    {
        DB::transaction(function () use ($leitor, $dados): void {
            $usuario = $leitor->usuario;
            $enderecoAnterior = $usuario->endereco;
            $endereco = $this->enderecoPara($dados);

            $usuario->fill(Arr::only($dados, self::CAMPOS_DO_USUARIO));
            $usuario->endereco_id = $endereco->id;

            if (filled($dados['senha'] ?? null)) {
                $usuario->senha = $dados['senha'];
            }

            $usuario->save();

            if ($enderecoAnterior->isNot($endereco)) {
                $this->removerSeOrfao($enderecoAnterior);
            }
        });
    }

    public function inativar(Leitor $leitor): void
    {
        $leitor->usuario->update(['situacao' => SituacaoUsuario::Inativo]);
    }

    /**
     * Se o leitor deixar de estar bloqueado, as filas paradas dele andam (R11).
     */
    public function reativar(Leitor $leitor): void
    {
        DB::transaction(function () use ($leitor): void {
            $leitor = Leitor::query()->lockForUpdate()->findOrFail($leitor->usuario_id);

            $estavaBloqueado = $leitor->estaBloqueado();

            $leitor->usuario->update(['situacao' => SituacaoUsuario::Ativo]);

            if ($estavaBloqueado && ! $leitor->estaBloqueado()) {
                LeitorDesbloqueado::dispatch($leitor);
            }
        });
    }

    /**
     * Reaproveita um endereço idêntico, se existir.
     *
     * @param  array<string, mixed>  $dados
     */
    private function enderecoPara(array $dados): Endereco
    {
        $campos = Arr::only($dados, self::CAMPOS_DO_ENDERECO);
        $campos['complemento'] ??= null;

        return Endereco::firstOrCreate($campos);
    }

    private function removerSeOrfao(Endereco $endereco): void
    {
        if (! $endereco->usuarios()->exists()) {
            $endereco->delete();
        }
    }
}
