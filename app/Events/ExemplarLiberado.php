<?php

namespace App\Events;

use App\Models\Exemplar;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * O exemplar ficou livre (devolução ou retirada vencida): a fila deve ser
 * atendida na mesma transação (R11, R12).
 */
class ExemplarLiberado
{
    use Dispatchable, SerializesModels;

    public function __construct(public Exemplar $exemplar) {}
}
