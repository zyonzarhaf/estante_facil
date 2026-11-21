<?php

namespace Tests\Feature;

use App\Enums\MotivoRecusa;
use App\Enums\SituacaoEmprestimo;
use App\Enums\SituacaoReserva;
use App\Enums\SituacaoUsuario;
use App\Events\ReservaAtendida;
use App\Exceptions\OperacaoRecusada;
use App\Mail\AvisoReservaAtendida;
use App\Models\Emprestimo;
use App\Models\Exemplar;
use App\Models\Leitor;
use App\Services\ServicoDeEmprestimo;
use App\Services\ServicoDeMulta;
use App\Services\ServicoDeReserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServicoDeEmprestimoTest extends TestCase
{
    use RefreshDatabase;

    private ServicoDeEmprestimo $servico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servico = app(ServicoDeEmprestimo::class);
    }

    public function test_emprestimo_direto_nasce_ativo_com_prazo_de_cinco_dias(): void
    {
        $bibliotecario = $this->criarBibliotecario();

        $emprestimo = $this->servico->emprestar($this->criarLeitor(), $this->criarExemplar(), $bibliotecario);

        $this->assertSame(SituacaoEmprestimo::Ativo, $emprestimo->situacao);
        $this->assertTrue($emprestimo->data_prevista_devolucao->isSameDay(today()->addDays(5)));
        $this->assertSame($bibliotecario->usuario_id, $emprestimo->bibliotecario_retirada_id);
    }

    public function test_exemplar_ocupado_nao_e_emprestado(): void
    {
        $exemplar = $this->criarExemplar();
        $this->emprestimoAtivo($this->criarLeitor(), $exemplar);

        $this->assertRecusa(MotivoRecusa::ExemplarOcupado,
            fn () => $this->servico->emprestar($this->criarLeitor(), $exemplar, $this->criarBibliotecario()));
    }

    public function test_retirada_dentro_do_prazo_ativa_o_emprestimo(): void
    {
        $emprestimo = $this->reservaAtendida($this->criarLeitor(), $this->criarExemplar());

        $emprestimo = $this->servico->registrarRetirada($emprestimo, $this->criarBibliotecario());

        $this->assertSame(SituacaoEmprestimo::Ativo, $emprestimo->situacao);
        $this->assertTrue($emprestimo->data_prevista_devolucao->isSameDay(today()->addDays(5)));
    }

    public function test_retirada_com_prazo_vencido_e_recusada(): void
    {
        $emprestimo = $this->reservaAtendida($this->criarLeitor(), $this->criarExemplar());
        $this->travel(4)->days();

        $this->assertRecusa(MotivoRecusa::PrazoVencido,
            fn () => $this->servico->registrarRetirada($emprestimo, $this->criarBibliotecario()));
    }

    public function test_renovacao_conta_a_partir_da_data_da_renovacao_e_tem_limite_de_tres(): void
    {
        $leitor = $this->criarLeitor();
        $emprestimo = $this->emprestimoAtivo($leitor, $this->criarExemplar(), diasAtras: 2);

        foreach (range(1, 3) as $ignorado) {
            $emprestimo = $this->servico->renovar($leitor, $emprestimo);
        }

        $this->assertTrue($emprestimo->data_prevista_devolucao->isSameDay(today()->addDays(5)));
        $this->assertSame(3, $emprestimo->quantidadeRenovacoes());
        $this->assertRecusa(MotivoRecusa::LimiteRenovacoes, fn () => $this->servico->renovar($leitor, $emprestimo));
    }

    public function test_renovacao_e_recusada_com_fila_ativa_e_permitida_com_fila_parada(): void
    {
        $leitor = $this->criarLeitor();
        $exemplar = $this->criarExemplar();
        $emprestimo = $this->emprestimoAtivo($leitor, $exemplar);
        $outroLeitor = $this->criarLeitor();
        app(ServicoDeReserva::class)->reservar($outroLeitor, $exemplar);

        $this->assertRecusa(MotivoRecusa::ExemplarComFila, fn () => $this->servico->renovar($leitor, $emprestimo));

        $outroLeitor->usuario->update(['situacao' => SituacaoUsuario::Inativo]);

        $this->assertSame(1, $this->servico->renovar($leitor, $emprestimo)->quantidadeRenovacoes());
    }

    public function test_devolucao_com_atraso_gera_multa_de_cinquenta_centavos_por_dia(): void
    {
        $emprestimo = $this->emprestimoAtivo($this->criarLeitor(), $this->criarExemplar(), diasAtras: 8);

        $emprestimo = $this->servico->registrarDevolucao($emprestimo, $this->criarBibliotecario());

        $this->assertSame(SituacaoEmprestimo::Encerrado, $emprestimo->situacao);
        $this->assertSame(3, $emprestimo->multa->diasDeAtraso());
        $this->assertSame(1.5, $emprestimo->multa->valorTotal());
    }

    public function test_devolucao_atende_o_primeiro_da_fila_nao_bloqueado_e_avisa_por_email(): void
    {
        Mail::fake();
        $exemplar = $this->criarExemplar();
        $emprestimo = $this->emprestimoAtivo($this->criarLeitor(), $exemplar);
        $bloqueado = $this->criarLeitor();
        $proximo = $this->criarLeitor();
        app(ServicoDeReserva::class)->reservar($bloqueado, $exemplar);
        app(ServicoDeReserva::class)->reservar($proximo, $exemplar);
        $bloqueado->usuario->update(['situacao' => SituacaoUsuario::Inativo]);

        $this->servico->registrarDevolucao($emprestimo, $this->criarBibliotecario());

        $this->assertSame(SituacaoReserva::NaFila, $bloqueado->reservas()->first()->situacao);
        $this->assertSame(SituacaoReserva::Atendida, $proximo->reservas()->first()->situacao);
        $this->assertSame($proximo->usuario_id, $exemplar->refresh()->emprestimoEmCurso->leitor_id);
        Mail::assertSent(AvisoReservaAtendida::class, fn ($email) => $email->hasTo($proximo->usuario->email));
    }

    public function test_pagamento_da_multa_faz_a_fila_parada_andar(): void
    {
        Event::fake([ReservaAtendida::class]);
        $leitor = $this->criarLeitor();
        $atrasado = $this->emprestimoAtivo($leitor, $this->criarExemplar());
        $exemplarDesejado = $this->criarExemplar();
        $outro = $this->emprestimoAtivo($this->criarLeitor(), $exemplarDesejado);
        app(ServicoDeReserva::class)->reservar($leitor, $exemplarDesejado);
        $this->travel(8)->days();

        $this->servico->registrarDevolucao($atrasado, $this->criarBibliotecario());
        $this->servico->registrarDevolucao($outro, $this->criarBibliotecario());
        $this->assertTrue($exemplarDesejado->refresh()->estaLivre(), 'Fila parada: o leitor está bloqueado pela multa.');

        app(ServicoDeMulta::class)->registrarPagamento($atrasado, $this->criarBibliotecario());

        $this->assertSame($leitor->usuario_id, $exemplarDesejado->refresh()->emprestimoEmCurso->leitor_id);
        Event::assertDispatched(ReservaAtendida::class);
    }

    public function test_retiradas_vencidas_sao_encerradas_e_a_fila_anda(): void
    {
        $exemplar = $this->criarExemplar();
        $emprestimo = $this->reservaAtendida($this->criarLeitor(), $exemplar);
        $proximo = $this->criarLeitor();
        app(ServicoDeReserva::class)->reservar($proximo, $exemplar);
        $this->travel(4)->days();

        $this->assertSame(1, $this->servico->encerrarRetiradasVencidas());

        $this->assertSame(SituacaoEmprestimo::Encerrado, $emprestimo->refresh()->situacao);
        $this->assertNull($emprestimo->data_retirada);
        $this->assertSame($proximo->usuario_id, $exemplar->refresh()->emprestimoEmCurso->leitor_id);
    }

    private function reservaAtendida(Leitor $leitor, Exemplar $exemplar): Emprestimo
    {
        return app(ServicoDeReserva::class)->reservar($leitor, $exemplar)->emprestimos()->first();
    }

    private function assertRecusa(MotivoRecusa $motivo, callable $operacao): void
    {
        try {
            $operacao();
            $this->fail("Era esperada a recusa {$motivo->value}.");
        } catch (OperacaoRecusada $recusa) {
            $this->assertSame($motivo, $recusa->motivo);
        }
    }
}
