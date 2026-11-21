<?php

namespace App\Listeners;

use App\Events\ReservaAtendida;
use App\Mail\AvisoReservaAtendida;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Mail;

/**
 * Único processamento assíncrono (RNF13): vai para a fila de trabalhos após
 * o COMMIT e é executado pelo worker.
 */
class EnviarAvisoReservaAtendida implements ShouldQueueAfterCommit
{
    public int $tries = 3;

    public function handle(ReservaAtendida $evento): void
    {
        $reserva = $evento->reserva->load('leitor.usuario', 'exemplar.edicao.obra', 'emprestimos');

        Mail::to($reserva->leitor->usuario)->send(new AvisoReservaAtendida($reserva));
    }
}
