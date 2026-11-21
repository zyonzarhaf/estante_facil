<?php

namespace App\Events;

use App\Models\Leitor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * O leitor deixou de estar bloqueado (pagamento de multa, devolução do
 * empréstimo atrasado ou reativação): as filas paradas em que ele está
 * devem ser reavaliadas na mesma transação (R11).
 */
class LeitorDesbloqueado
{
    use Dispatchable, SerializesModels;

    public function __construct(public Leitor $leitor) {}
}
