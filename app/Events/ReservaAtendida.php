<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A fila atendeu uma reserva: o leitor deve ser avisado por e-mail (R18).
 */
class ReservaAtendida
{
    use Dispatchable, SerializesModels;

    public function __construct(public Reserva $reserva) {}
}
