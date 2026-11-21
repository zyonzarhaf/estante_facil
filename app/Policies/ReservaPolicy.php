<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\Usuario;

class ReservaPolicy
{
    /**
     * Só o leitor da reserva pode cancelá-la.
     */
    public function cancelar(Usuario $usuario, Reserva $reserva): bool
    {
        return $usuario->ehLeitor() && $reserva->leitor_id === $usuario->id;
    }
}
