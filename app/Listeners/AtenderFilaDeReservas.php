<?php

namespace App\Listeners;

use App\Events\ExemplarLiberado;
use App\Services\ServicoDeFilaDeReservas;

/**
 * Listener síncrono: roda na transação de quem disparou o evento (RNF12).
 */
class AtenderFilaDeReservas
{
    public function __construct(private ServicoDeFilaDeReservas $servicoDeFilaDeReservas) {}

    public function handle(ExemplarLiberado $evento): void
    {
        $this->servicoDeFilaDeReservas->atenderFila($evento->exemplar);
    }
}
