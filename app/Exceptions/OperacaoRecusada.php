<?php

namespace App\Exceptions;

use App\Enums\MotivoRecusa;
use Exception;
use Illuminate\Http\RedirectResponse;

/**
 * Lançada pelos serviços quando uma regra de negócio recusa a operação.
 * Por ser lançada dentro de DB::transaction(), provoca o rollback.
 */
class OperacaoRecusada extends Exception
{
    public function __construct(public readonly MotivoRecusa $motivo)
    {
        parent::__construct($motivo->mensagem());
    }

    /**
     * Volta à página anterior exibindo o motivo da recusa (RF17).
     */
    public function render(): RedirectResponse
    {
        return back()->with('erro', $this->getMessage());
    }
}
