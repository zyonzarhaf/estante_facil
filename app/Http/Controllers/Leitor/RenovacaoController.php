<?php

namespace App\Http\Controllers\Leitor;

use App\Http\Controllers\Controller;
use App\Models\Emprestimo;
use App\Services\ServicoDeEmprestimo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * RF06: renovação pelo próprio leitor.
 */
class RenovacaoController extends Controller
{
    public function __construct(private ServicoDeEmprestimo $servicoDeEmprestimo) {}

    public function __invoke(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        Gate::authorize('renovar', $emprestimo);

        $emprestimo = $this->servicoDeEmprestimo->renovar($request->user()->leitor, $emprestimo);

        return back()->with('sucesso', 'Empréstimo renovado. Nova data de devolução: '
            .$emprestimo->data_prevista_devolucao->format('d/m/Y').'.');
    }
}
