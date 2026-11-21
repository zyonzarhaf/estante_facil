<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Models\Emprestimo;
use App\Services\ServicoDeMulta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * RF11: pagamento de multa, pela raiz do agregado Empréstimo.
 */
class PagamentoDeMultaController extends Controller
{
    public function __construct(private ServicoDeMulta $servicoDeMulta) {}

    public function __invoke(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        $this->servicoDeMulta->registrarPagamento($emprestimo, $request->user()->bibliotecario);

        return back()->with('sucesso', 'Pagamento da multa registrado.');
    }
}
