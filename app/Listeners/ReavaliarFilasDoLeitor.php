<?php

namespace App\Listeners;

use App\Events\LeitorDesbloqueado;
use App\Services\ServicoDeFilaDeReservas;

/**
 * Listener síncrono: roda na transação de quem disparou o evento (RNF12).
 */
class ReavaliarFilasDoLeitor
{
    public function __construct(private ServicoDeFilaDeReservas $servicoDeFilaDeReservas) {}

    public function handle(LeitorDesbloqueado $evento): void
    {
        $this->servicoDeFilaDeReservas->reavaliarFilas($evento->leitor);
    }
}
