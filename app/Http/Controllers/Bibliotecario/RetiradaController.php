<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Models\Emprestimo;
use App\Services\ServicoDeEmprestimo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * RF09: retirada de exemplar reservado.
 */
class RetiradaController extends Controller
{
    public function __construct(private ServicoDeEmprestimo $servicoDeEmprestimo) {}

    public function __invoke(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        $emprestimo = $this->servicoDeEmprestimo->registrarRetirada($emprestimo, $request->user()->bibliotecario);

        return back()->with('sucesso', 'Retirada registrada. Devolução até '
            .$emprestimo->data_prevista_devolucao->format('d/m/Y').'.');
    }
}
