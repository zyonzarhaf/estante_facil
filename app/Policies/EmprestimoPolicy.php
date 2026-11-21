<?php

namespace App\Policies;

use App\Models\Emprestimo;
use App\Models\Usuario;

class EmprestimoPolicy
{
    /**
     * Só o leitor do empréstimo pode renová-lo.
     */
    public function renovar(Usuario $usuario, Emprestimo $emprestimo): bool
    {
        return $usuario->ehLeitor() && $emprestimo->leitor_id === $usuario->id;
    }
}
