<?php

namespace Tests\Feature;

use App\Enums\SituacaoEmprestimo;
use App\Enums\SituacaoReserva;
use App\Models\Endereco;
use App\Services\ServicoDeExemplar;
use App\Services\ServicoDeLeitor;
use App\Services\ServicoDeReserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CadastrosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private array $endereco = [
        'logradouro' => 'Av. Boa Viagem', 'numero' => '100', 'bairro' => 'Boa Viagem',
        'cidade' => 'Recife', 'uf' => 'PE', 'cep' => '51020000',
    ];

    public function test_cadastro_de_leitor_grava_usuario_leitor_e_endereco_compartilhado(): void
    {
        $servico = app(ServicoDeLeitor::class);

        $ana = $servico->cadastrar([...$this->endereco, 'nome' => 'Ana', 'cpf' => '12345678901', 'email' => 'ana@exemplo.com', 'senha' => 'senha-secreta']);
        $bia = $servico->cadastrar([...$this->endereco, 'nome' => 'Bia', 'cpf' => '12345678902', 'email' => 'bia@exemplo.com', 'senha' => 'senha-secreta']);

        $this->assertTrue($ana->usuario->ehLeitor());
        $this->assertSame($ana->usuario->endereco_id, $bia->usuario->endereco_id);
    }

    public function test_endereco_que_fica_sem_usuario_e_removido(): void
    {
        $servico = app(ServicoDeLeitor::class);
        $leitor = $servico->cadastrar([...$this->endereco, 'nome' => 'Ana', 'cpf' => '12345678901', 'email' => 'ana@exemplo.com', 'senha' => 'senha-secreta']);
        $enderecoAntigo = $leitor->usuario->endereco_id;

        $servico->atualizar($leitor, [...$this->endereco, 'numero' => '200', 'nome' => 'Ana', 'cpf' => '12345678901', 'email' => 'ana@exemplo.com']);

        $this->assertNull(Endereco::find($enderecoAntigo));
    }

    public function test_exemplar_indisponivel_cancela_a_fila_e_encerra_a_retirada_pendente(): void
    {
        $exemplar = $this->criarExemplar();
        $aguardando = app(ServicoDeReserva::class)->reservar($this->criarLeitor(), $exemplar)->emprestimos()->first();
        $naFila = app(ServicoDeReserva::class)->reservar($this->criarLeitor(), $exemplar);

        app(ServicoDeExemplar::class)->marcarIndisponivel($exemplar);

        $this->assertSame(SituacaoEmprestimo::Encerrado, $aguardando->refresh()->situacao);
        $this->assertSame(SituacaoReserva::Cancelada, $naFila->refresh()->situacao);
    }
}
