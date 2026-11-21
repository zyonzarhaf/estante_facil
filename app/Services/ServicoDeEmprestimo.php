<?php

namespace App\Services;

use App\Enums\MotivoRecusa;
use App\Enums\SituacaoEmprestimo;
use App\Events\ExemplarLiberado;
use App\Events\LeitorDesbloqueado;
use App\Exceptions\OperacaoRecusada;
use App\Models\Bibliotecario;
use App\Models\Emprestimo;
use App\Models\Exemplar;
use App\Models\Leitor;
use Illuminate\Support\Facades\DB;

/**
 * Empréstimo direto, retirada, renovação, devolução e encerramento de
 * retiradas vencidas (R1–R8, R12, R13, R16).
 *
 * Ordem das travas, para evitar deadlocks: leitor → exemplar → empréstimo.
 */
class ServicoDeEmprestimo
{
    public function __construct(private ServicoDeFilaDeReservas $servicoDeFilaDeReservas) {}

    /**
     * RF08: empréstimo direto no balcão.
     *
     * @throws OperacaoRecusada
     */
    public function emprestar(Leitor $leitor, Exemplar $exemplar, Bibliotecario $bibliotecario): Emprestimo
    {
        return DB::transaction(function () use ($leitor, $exemplar, $bibliotecario): Emprestimo {
            $leitor = Leitor::query()->lockForUpdate()->findOrFail($leitor->usuario_id);
            $exemplar = Exemplar::query()->lockForUpdate()->findOrFail($exemplar->id);

            if ($motivo = $this->validarEmprestimo($leitor, $exemplar)) {
                throw new OperacaoRecusada($motivo);
            }

            return Emprestimo::noBalcao($leitor, $exemplar, $bibliotecario);
        });
    }

    /**
     * R7: só exemplar livre; com fila parada (todos bloqueados) é permitido.
     */
    public function validarEmprestimo(Leitor $leitor, Exemplar $exemplar): ?MotivoRecusa
    {
        return match (true) {
            $leitor->estaBloqueado() => MotivoRecusa::LeitorBloqueado,
            $leitor->atingiuLimite() => MotivoRecusa::LimiteAtingido,
            $exemplar->estaIndisponivel() => MotivoRecusa::ExemplarIndisponivel,
            ! $exemplar->estaLivre() => MotivoRecusa::ExemplarOcupado,
            $this->servicoDeFilaDeReservas->temFilaAtiva($exemplar) => MotivoRecusa::ExemplarComFila,
            default => null,
        };
    }

    /**
     * RF09: AGUARDANDO_RETIRADA → ATIVO.
     *
     * @throws OperacaoRecusada
     */
    public function registrarRetirada(Emprestimo $emprestimo, Bibliotecario $bibliotecario): Emprestimo
    {
        return DB::transaction(function () use ($emprestimo, $bibliotecario): Emprestimo {
            Leitor::query()->lockForUpdate()->findOrFail($emprestimo->leitor_id);
            $emprestimo = Emprestimo::query()->lockForUpdate()->findOrFail($emprestimo->id);

            if ($motivo = $this->validarRetirada($emprestimo)) {
                throw new OperacaoRecusada($motivo);
            }

            $emprestimo->registrarRetirada($bibliotecario);

            return $emprestimo;
        });
    }

    public function validarRetirada(Emprestimo $emprestimo): ?MotivoRecusa
    {
        return match (true) {
            ! $emprestimo->estaAguardandoRetirada() => MotivoRecusa::EmprestimoInvalido,
            $emprestimo->prazoRetiradaVencido() => MotivoRecusa::PrazoVencido,
            $emprestimo->leitor->estaBloqueado() => MotivoRecusa::LeitorBloqueado,
            $emprestimo->leitor->atingiuLimite() => MotivoRecusa::LimiteAtingido,
            default => null,
        };
    }

    /**
     * RF06: renovação feita pelo próprio leitor.
     *
     * @throws OperacaoRecusada
     */
    public function renovar(Leitor $leitor, Emprestimo $emprestimo): Emprestimo
    {
        return DB::transaction(function () use ($leitor, $emprestimo): Emprestimo {
            $emprestimo = Emprestimo::query()->lockForUpdate()->findOrFail($emprestimo->id);

            if ($motivo = $this->validarRenovacao($leitor, $emprestimo)) {
                throw new OperacaoRecusada($motivo);
            }

            $emprestimo->renovar();

            return $emprestimo;
        });
    }

    public function validarRenovacao(Leitor $leitor, Emprestimo $emprestimo): ?MotivoRecusa
    {
        return match (true) {
            ! $emprestimo->podeSerRenovadoPor($leitor) => MotivoRecusa::EmprestimoInvalido,
            $leitor->estaBloqueado() => MotivoRecusa::LeitorBloqueado,
            $emprestimo->quantidadeRenovacoes() >= config('biblioteca.max_renovacoes') => MotivoRecusa::LimiteRenovacoes,
            $this->servicoDeFilaDeReservas->temFilaAtiva($emprestimo->exemplar) => MotivoRecusa::ExemplarComFila,
            default => null,
        };
    }

    /**
     * RF10: ATIVO → ENCERRADO; multa se houver atraso; atende a fila do
     * exemplar e, se o leitor deixou de estar bloqueado, as filas paradas dele.
     *
     * @throws OperacaoRecusada
     */
    public function registrarDevolucao(Emprestimo $emprestimo, Bibliotecario $bibliotecario): Emprestimo
    {
        return DB::transaction(function () use ($emprestimo, $bibliotecario): Emprestimo {
            $leitor = Leitor::query()->lockForUpdate()->findOrFail($emprestimo->leitor_id);
            $exemplar = Exemplar::query()->lockForUpdate()->findOrFail($emprestimo->exemplar_id);
            $emprestimo = Emprestimo::query()->lockForUpdate()->findOrFail($emprestimo->id);

            $estavaBloqueado = $leitor->estaBloqueado();

            $emprestimo->registrarDevolucao($bibliotecario);

            ExemplarLiberado::dispatch($exemplar);

            if ($estavaBloqueado && ! $leitor->estaBloqueado()) {
                LeitorDesbloqueado::dispatch($leitor);
            }

            return $emprestimo;
        });
    }

    /**
     * R12: rotina diária. Cada empréstimo é encerrado na sua própria transação.
     *
     * @return int quantidade de empréstimos encerrados
     */
    public function encerrarRetiradasVencidas(): int
    {
        $ids = Emprestimo::query()
            ->where('situacao', SituacaoEmprestimo::AguardandoRetirada)
            ->whereDate('data_limite_retirada', '<', today())
            ->pluck('id');

        $encerrados = 0;

        foreach ($ids as $id) {
            $encerrados += DB::transaction(function () use ($id): int {
                $emprestimo = Emprestimo::query()->findOrFail($id);
                $exemplar = Exemplar::query()->lockForUpdate()->findOrFail($emprestimo->exemplar_id);
                $emprestimo = Emprestimo::query()->lockForUpdate()->findOrFail($id);

                if (! $emprestimo->prazoRetiradaVencido()) {
                    return 0;
                }

                $emprestimo->encerrarSemRetirada();

                ExemplarLiberado::dispatch($exemplar);

                return 1;
            });
        }

        return $encerrados;
    }
}
