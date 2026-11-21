<?php

namespace Database\Seeders;

use App\Enums\SituacaoEmprestimo;
use App\Events\ReservaAtendida;
use App\Exceptions\OperacaoRecusada;
use App\Models\Bibliotecario;
use App\Models\Emprestimo;
use App\Models\Exemplar;
use App\Models\Leitor;
use App\Models\Multa;
use App\Models\Reserva;
use App\Models\Usuario;
use App\Services\ServicoDeEmprestimo;
use App\Services\ServicoDeLeitor;
use App\Services\ServicoDeMulta;
use App\Services\ServicoDeReserva;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

/**
 * Simula alguns meses de funcionamento da biblioteca chamando os próprios
 * serviços de domínio, dia após dia. Assim empréstimos, renovações, multas,
 * reservas e filas respeitam todas as regras de negócio.
 */
class CirculacaoSeeder extends Seeder
{
    private const DIAS_SIMULADOS = 120;

    /** @var Collection<int, int> */
    private Collection $idsDosLeitores;

    /** @var Collection<int, int> */
    private Collection $idsDosExemplares;

    /** @var Collection<int, Bibliotecario> */
    private Collection $bibliotecarios;

    public function __construct(
        private ServicoDeEmprestimo $servicoDeEmprestimo,
        private ServicoDeReserva $servicoDeReserva,
        private ServicoDeMulta $servicoDeMulta,
        private ServicoDeLeitor $servicoDeLeitor,
    ) {}

    public function run(): void
    {
        // Os avisos por e-mail (R18) não são enviados durante a simulação.
        Event::fake([ReservaAtendida::class]);

        $this->idsDosLeitores = Leitor::query()->pluck('usuario_id');
        $this->idsDosExemplares = Exemplar::query()->pluck('id');
        $this->bibliotecarios = Bibliotecario::all();

        $inicio = today()->subDays(self::DIAS_SIMULADOS);

        for ($dia = 0; $dia <= self::DIAS_SIMULADOS; $dia++) {
            Carbon::setTestNow($inicio->copy()->addDays($dia)->setTime(9, 0));

            $this->servicoDeEmprestimo->encerrarRetiradasVencidas();
            $this->registrarRetiradas();
            $this->registrarDevolucoes();
            $this->registrarPagamentos();
            $this->renovar();
            $this->emprestarNoBalcao(fake()->numberBetween(5, 9));
            $this->reservar(fake()->numberBetween(2, 4));
            $this->cancelarReservas();
            $this->alterarSituacaoDeLeitores();
        }

        $this->prepararContaDeDemonstracao();

        Carbon::setTestNow();
    }

    private function registrarRetiradas(): void
    {
        foreach (Emprestimo::query()->where('situacao', SituacaoEmprestimo::AguardandoRetirada)->get() as $emprestimo) {
            if (fake()->boolean(55)) {
                $this->tentar(fn () => $this->servicoDeEmprestimo->registrarRetirada($emprestimo, $this->bibliotecario()));
            }
        }
    }

    private function registrarDevolucoes(): void
    {
        foreach (Emprestimo::query()->ativos()->get() as $emprestimo) {
            $chance = match (true) {
                $emprestimo->estaAtrasado() => 10,
                $emprestimo->data_prevista_devolucao->diffInDays(today()) <= 1 => 22,
                default => 3,
            };

            if (fake()->boolean($chance)) {
                $this->tentar(fn () => $this->servicoDeEmprestimo->registrarDevolucao($emprestimo, $this->bibliotecario()));
            }
        }
    }

    private function registrarPagamentos(): void
    {
        foreach (Multa::query()->pendentes()->with('emprestimo')->get() as $multa) {
            if (fake()->boolean(15)) {
                $this->tentar(fn () => $this->servicoDeMulta->registrarPagamento($multa->emprestimo, $this->bibliotecario()));
            }
        }
    }

    private function renovar(): void
    {
        $vencendo = Emprestimo::query()
            ->ativos()
            ->whereDate('data_prevista_devolucao', '<=', today()->addDay())
            ->whereDate('data_prevista_devolucao', '>=', today())
            ->with('leitor')
            ->get();

        foreach ($vencendo as $emprestimo) {
            if (fake()->boolean(70)) {
                $this->tentar(fn () => $this->servicoDeEmprestimo->renovar($emprestimo->leitor, $emprestimo));
            }
        }
    }

    private function emprestarNoBalcao(int $tentativas): void
    {
        foreach (range(1, $tentativas) as $ignorado) {
            $this->tentar(fn () => $this->servicoDeEmprestimo->emprestar(
                Leitor::find($this->idsDosLeitores->random()),
                Exemplar::find($this->idsDosExemplares->random()),
                $this->bibliotecario(),
            ));
        }
    }

    /**
     * Reservas tendem a mirar exemplares emprestados, para formar filas.
     */
    private function reservar(int $tentativas): void
    {
        foreach (range(1, $tentativas) as $ignorado) {
            $ocupado = Emprestimo::query()->emCurso()->inRandomOrder()->value('exemplar_id');
            $idDoExemplar = $ocupado !== null && fake()->boolean(75) ? $ocupado : $this->idsDosExemplares->random();

            $this->tentar(fn () => $this->servicoDeReserva->reservar(
                Leitor::find($this->idsDosLeitores->random()),
                Exemplar::find($idDoExemplar),
            ));
        }
    }

    private function cancelarReservas(): void
    {
        foreach (Reserva::query()->naFila()->get() as $reserva) {
            if (fake()->boolean(3)) {
                $this->tentar(fn () => $this->servicoDeReserva->cancelar($reserva));
            }
        }
    }

    private function alterarSituacaoDeLeitores(): void
    {
        if (fake()->boolean(4)) {
            $this->servicoDeLeitor->inativar(Leitor::find($this->idsDosLeitores->random()));
        }

        $inativo = Leitor::query()->whereHas('usuario', fn ($usuario) => $usuario->where('situacao', 'INATIVO'))->inRandomOrder()->first();

        if ($inativo !== null && fake()->boolean(5)) {
            $this->servicoDeLeitor->reativar($inativo);
        }
    }

    /**
     * A conta de demonstração termina com empréstimos em curso, para a apresentação.
     */
    private function prepararContaDeDemonstracao(): void
    {
        $leitor = Usuario::query()->where('email', 'leitor@biblioteca.local')->firstOrFail()->leitor;
        $this->servicoDeLeitor->reativar($leitor);

        foreach ($leitor->emprestimos()->atrasados()->get() as $atrasado) {
            $this->servicoDeEmprestimo->registrarDevolucao($atrasado, $this->bibliotecario());
        }

        foreach (Multa::query()->pendentes()->whereHas('emprestimo', fn ($consulta) => $consulta->where('leitor_id', $leitor->usuario_id))->with('emprestimo')->get() as $multa) {
            $this->servicoDeMulta->registrarPagamento($multa->emprestimo, $this->bibliotecario());
        }

        $livres = Exemplar::query()->inRandomOrder()->get()->filter(fn (Exemplar $exemplar) => $exemplar->estaLivre())->take(2);

        foreach ($livres as $exemplar) {
            $this->tentar(fn () => $this->servicoDeEmprestimo->emprestar($leitor, $exemplar, $this->bibliotecario()));
        }
    }

    private function bibliotecario(): Bibliotecario
    {
        return $this->bibliotecarios->random();
    }

    /**
     * Operações recusadas pelas regras de negócio fazem parte da simulação.
     */
    private function tentar(callable $operacao): void
    {
        try {
            $operacao();
        } catch (OperacaoRecusada) {
            //
        }

        Carbon::setTestNow(now()->addMinutes(fake()->numberBetween(1, 15)));
    }
}
