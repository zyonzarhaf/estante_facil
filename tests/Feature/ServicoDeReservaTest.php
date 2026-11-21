<?php

namespace Tests\Feature;

use App\Enums\MotivoRecusa;
use App\Enums\SituacaoEmprestimo;
use App\Enums\SituacaoReserva;
use App\Events\ReservaAtendida;
use App\Exceptions\OperacaoRecusada;
use App\Services\ServicoDeReserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ServicoDeReservaTest extends TestCase
{
    use RefreshDatabase;

    private ServicoDeReserva $servico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servico = app(ServicoDeReserva::class);
    }

    public function test_reserva_de_exemplar_livre_e_atendida_na_hora_sem_aviso(): void
    {
        Event::fake([ReservaAtendida::class]);
        $leitor = $this->criarLeitor();
        $exemplar = $this->criarExemplar();

        $reserva = $this->servico->reservar($leitor, $exemplar);

        $this->assertSame(SituacaoReserva::Atendida, $reserva->situacao);
        $emprestimo = $reserva->emprestimos()->first();
        $this->assertSame(SituacaoEmprestimo::AguardandoRetirada, $emprestimo->situacao);
        $this->assertTrue($emprestimo->data_limite_retirada->isSameDay(today()->addDays(3)));
        Event::assertNotDispatched(ReservaAtendida::class);
    }

    public function test_reserva_de_exemplar_ocupado_entra_na_fila(): void
    {
        $exemplar = $this->criarExemplar();
        $this->emprestimoAtivo($this->criarLeitor(), $exemplar);

        $primeira = $this->servico->reservar($this->criarLeitor(), $exemplar);
        $segunda = $this->servico->reservar($this->criarLeitor(), $exemplar);

        $this->assertSame(SituacaoReserva::NaFila, $primeira->situacao);
        $this->assertSame(1, $primeira->posicaoNaFila());
        $this->assertSame(2, $segunda->posicaoNaFila());
    }

    public function test_leitor_com_multa_pendente_nao_reserva(): void
    {
        $leitor = $this->criarLeitor();
        $emprestimo = $this->emprestimoAtivo($leitor, $this->criarExemplar(), diasAtras: 7);
        $emprestimo->registrarDevolucao($this->criarBibliotecario());

        $this->assertRecusa(MotivoRecusa::LeitorBloqueado, fn () => $this->servico->reservar($leitor, $this->criarExemplar()));
    }

    public function test_leitor_com_cinco_emprestimos_ativos_nao_reserva(): void
    {
        $leitor = $this->criarLeitor();
        foreach (range(1, 5) as $ignorado) {
            $this->emprestimoAtivo($leitor, $this->criarExemplar());
        }

        $this->assertRecusa(MotivoRecusa::LimiteAtingido, fn () => $this->servico->reservar($leitor, $this->criarExemplar()));
    }

    public function test_exemplar_indisponivel_nao_pode_ser_reservado(): void
    {
        $exemplar = $this->criarExemplar();
        $exemplar->marcarIndisponivel();

        $this->assertRecusa(MotivoRecusa::ExemplarIndisponivel, fn () => $this->servico->reservar($this->criarLeitor(), $exemplar));
    }

    public function test_leitor_nao_reserva_o_mesmo_exemplar_duas_vezes(): void
    {
        $leitor = $this->criarLeitor();
        $exemplar = $this->criarExemplar();
        $this->emprestimoAtivo($this->criarLeitor(), $exemplar);
        $this->servico->reservar($leitor, $exemplar);

        $this->assertRecusa(MotivoRecusa::ReservaDuplicada, fn () => $this->servico->reservar($leitor, $exemplar));
    }

    public function test_reserva_na_fila_pode_ser_cancelada_e_depois_nao(): void
    {
        $exemplar = $this->criarExemplar();
        $this->emprestimoAtivo($this->criarLeitor(), $exemplar);
        $reserva = $this->servico->reservar($this->criarLeitor(), $exemplar);

        $this->servico->cancelar($reserva);

        $this->assertSame(SituacaoReserva::Cancelada, $reserva->refresh()->situacao);
        $this->assertRecusa(MotivoRecusa::ReservaNaoCancelavel, fn () => $this->servico->cancelar($reserva));
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
