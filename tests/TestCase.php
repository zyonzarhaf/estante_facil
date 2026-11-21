<?php

namespace Tests;

use App\Enums\EstadoConservacao;
use App\Enums\Perfil;
use App\Models\Bibliotecario;
use App\Models\Edicao;
use App\Models\Editora;
use App\Models\Emprestimo;
use App\Models\Endereco;
use App\Models\Exemplar;
use App\Models\Leitor;
use App\Models\Obra;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private int $sequencia = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Cenários mínimos montados com os models; factories e seeders de dados
     * fictícios ficam para uma etapa posterior.
     */
    protected function criarLeitor(): Leitor
    {
        return Leitor::create(['usuario_id' => $this->criarUsuario(Perfil::Leitor)->id]);
    }

    protected function criarBibliotecario(): Bibliotecario
    {
        return Bibliotecario::create(['usuario_id' => $this->criarUsuario(Perfil::Bibliotecario)->id]);
    }

    protected function criarExemplar(?Edicao $edicao = null): Exemplar
    {
        $numero = ++$this->sequencia;

        return Exemplar::create([
            'codigo_tombo' => 'T'.str_pad((string) $numero, 5, '0', STR_PAD_LEFT),
            'edicao_id' => ($edicao ?? $this->criarEdicao())->id,
            'data_aquisicao' => today(),
            'estado_conservacao' => EstadoConservacao::Bom,
        ]);
    }

    protected function criarEdicao(): Edicao
    {
        $numero = ++$this->sequencia;
        $obra = Obra::create(['titulo' => "Obra {$numero}"]);
        $editora = Editora::create(['nome' => "Editora {$numero}"]);

        return Edicao::create([
            'isbn' => str_pad((string) $numero, 13, '9', STR_PAD_LEFT),
            'obra_id' => $obra->id,
            'editora_id' => $editora->id,
            'numero_edicao' => 1,
        ]);
    }

    /**
     * Empréstimo ATIVO retirado há $diasAtras dias (com prazo de 5 dias).
     */
    protected function emprestimoAtivo(Leitor $leitor, Exemplar $exemplar, int $diasAtras = 0): Emprestimo
    {
        $this->travel(-$diasAtras)->days();
        $emprestimo = Emprestimo::noBalcao($leitor, $exemplar, $this->criarBibliotecario());
        $this->travelBack();

        return $emprestimo->refresh();
    }

    private function criarUsuario(Perfil $perfil): Usuario
    {
        $numero = ++$this->sequencia;

        $endereco = Endereco::create([
            'logradouro' => 'Rua das Flores', 'numero' => (string) $numero, 'bairro' => 'Centro',
            'cidade' => 'Recife', 'uf' => 'PE', 'cep' => '50000000',
        ]);

        return Usuario::create([
            'nome' => "Usuário {$numero}",
            'cpf' => str_pad((string) $numero, 11, '0', STR_PAD_LEFT),
            'email' => "usuario{$numero}@exemplo.com",
            'endereco_id' => $endereco->id,
            'senha' => 'senha-secreta',
            'perfil' => $perfil,
        ]);
    }
}
