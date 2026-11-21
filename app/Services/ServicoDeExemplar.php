<?php

namespace App\Services;

use App\Models\Exemplar;
use Illuminate\Support\Facades\DB;

/**
 * RF13: indisponibilidade de exemplares (R13, R14).
 */
class ServicoDeExemplar
{
    /**
     * R14: cancela a fila e encerra a retirada pendente.
     */
    public function marcarIndisponivel(Exemplar $exemplar): void
    {
        DB::transaction(function () use ($exemplar): void {
            $exemplar = Exemplar::query()->lockForUpdate()->findOrFail($exemplar->id);

            $exemplar->marcarIndisponivel();

            foreach ($exemplar->reservas()->naFila()->get() as $reserva) {
                $reserva->cancelar();
            }

            $emprestimo = $exemplar->emprestimoEmCurso;

            if ($emprestimo?->estaAguardandoRetirada()) {
                $emprestimo->encerrarSemRetirada();
            }
        });
    }

    public function marcarDisponivel(Exemplar $exemplar): void
    {
        DB::transaction(function () use ($exemplar): void {
            Exemplar::query()->lockForUpdate()->findOrFail($exemplar->id)->marcarDisponivel();
        });
    }
}
