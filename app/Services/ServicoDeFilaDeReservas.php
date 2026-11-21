<?php

namespace App\Services;

use App\Events\ReservaAtendida;
use App\Models\Emprestimo;
use App\Models\Exemplar;
use App\Models\Leitor;
use App\Models\Reserva;

/**
 * Fila de reservas de cada exemplar (R11). Os métodos rodam dentro da
 * transação de quem os chama, com o exemplar já travado.
 */
class ServicoDeFilaDeReservas
{
    /**
     * Atende a reserva mais antiga de leitor não bloqueado. Leitores
     * bloqueados são pulados e continuam na fila.
     */
    public function atenderFila(Exemplar $exemplar): ?Reserva
    {
        if (! $exemplar->estaLivre()) {
            return null;
        }

        foreach ($exemplar->filaDeReservas()->with('leitor.usuario')->get() as $reserva) {
            if ($reserva->leitor->estaBloqueado()) {
                continue;
            }

            $this->atender($reserva, avisar: true);

            return $reserva;
        }

        return null;
    }

    /**
     * Filas paradas: exemplares livres em que o leitor tem reserva na fila.
     */
    public function reavaliarFilas(Leitor $leitor): void
    {
        $idsDosExemplares = $leitor->reservas()->naFila()->pluck('exemplar_id');

        foreach ($idsDosExemplares as $idDoExemplar) {
            $exemplar = Exemplar::query()->lockForUpdate()->findOrFail($idDoExemplar);

            $this->atenderFila($exemplar);
        }
    }

    /**
     * R3: há reserva na fila de algum leitor não bloqueado.
     */
    public function temFilaAtiva(Exemplar $exemplar): bool
    {
        return $exemplar->filaDeReservas()
            ->with('leitor.usuario')
            ->get()
            ->contains(fn (Reserva $reserva) => ! $reserva->leitor->estaBloqueado());
    }

    /**
     * Atende uma reserva: ela passa a ATENDIDA e nasce um empréstimo
     * AGUARDANDO_RETIRADA vinculado a ela. O aviso por e-mail (R18) só é
     * enviado quando o leitor não está presente, ou seja, quando é a fila
     * que o atende.
     */
    public function atender(Reserva $reserva, bool $avisar): Emprestimo
    {
        $reserva->atender();

        $emprestimo = Emprestimo::aguardandoRetirada($reserva->leitor, $reserva->exemplar);
        $emprestimo->vincular($reserva);

        if ($avisar) {
            ReservaAtendida::dispatch($reserva);
        }

        return $emprestimo;
    }
}
