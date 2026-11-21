<?php

namespace App\Services;

use App\Events\LeitorDesbloqueado;
use App\Exceptions\OperacaoRecusada;
use App\Models\Bibliotecario;
use App\Models\Emprestimo;
use App\Models\Leitor;
use Illuminate\Support\Facades\DB;

/**
 * RF11: pagamento de multa, pela raiz do agregado Empréstimo (R8, R16).
 */
class ServicoDeMulta
{
    /**
     * @throws OperacaoRecusada
     */
    public function registrarPagamento(Emprestimo $emprestimo, Bibliotecario $bibliotecario): void
    {
        DB::transaction(function () use ($emprestimo, $bibliotecario): void {
            $leitor = Leitor::query()->lockForUpdate()->findOrFail($emprestimo->leitor_id);
            $emprestimo = Emprestimo::query()->lockForUpdate()->findOrFail($emprestimo->id);

            $estavaBloqueado = $leitor->estaBloqueado();

            $emprestimo->quitarMulta($bibliotecario);

            if ($estavaBloqueado && ! $leitor->estaBloqueado()) {
                LeitorDesbloqueado::dispatch($leitor);
            }
        });
    }
}
