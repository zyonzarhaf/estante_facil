<?php

namespace App\Services;

use App\Enums\MotivoRecusa;
use App\Exceptions\OperacaoRecusada;
use App\Models\Exemplar;
use App\Models\Leitor;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

/**
 * Reservas feitas e canceladas pelo próprio leitor (R8, R9, R10, R13, R15).
 */
class ServicoDeReserva
{
    public function __construct(private ServicoDeFilaDeReservas $servicoDeFilaDeReservas) {}

    /**
     * R9: exemplar livre → reserva atendida na hora; ocupado → reserva na fila.
     *
     * @throws OperacaoRecusada
     */
    public function reservar(Leitor $leitor, Exemplar $exemplar): Reserva
    {
        return DB::transaction(function () use ($leitor, $exemplar): Reserva {
            $leitor = Leitor::query()->lockForUpdate()->findOrFail($leitor->usuario_id);
            $exemplar = Exemplar::query()->lockForUpdate()->findOrFail($exemplar->id);

            if ($motivo = $this->validarReserva($leitor, $exemplar)) {
                throw new OperacaoRecusada($motivo);
            }

            $reserva = Reserva::naFilaDe($leitor, $exemplar);

            if ($exemplar->estaLivre()) {
                $this->servicoDeFilaDeReservas->atender($reserva, avisar: false);
            }

            return $reserva->refresh();
        });
    }

    /**
     * Verificações em ordem de precedência; vale o primeiro motivo encontrado.
     */
    public function validarReserva(Leitor $leitor, Exemplar $exemplar): ?MotivoRecusa
    {
        return match (true) {
            $leitor->estaBloqueado() => MotivoRecusa::LeitorBloqueado,
            $leitor->atingiuLimite() => MotivoRecusa::LimiteAtingido,
            $exemplar->estaIndisponivel() => MotivoRecusa::ExemplarIndisponivel,
            $exemplar->temReservaOuEmprestimoDe($leitor) => MotivoRecusa::ReservaDuplicada,
            default => null,
        };
    }

    /**
     * @throws OperacaoRecusada
     */
    public function cancelar(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva): void {
            Reserva::query()->lockForUpdate()->findOrFail($reserva->id)->cancelar();
        });
    }
}
