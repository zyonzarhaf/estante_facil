<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Models\Emprestimo;
use App\Services\ServicoDeEmprestimo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

/**
 * RF10: devolução; gera multa se houver atraso e atende a fila.
 */
class DevolucaoController extends Controller
{
    public function __construct(private ServicoDeEmprestimo $servicoDeEmprestimo) {}

    public function __invoke(Request $request, Emprestimo $emprestimo): RedirectResponse
    {
        $emprestimo = $this->servicoDeEmprestimo->registrarDevolucao($emprestimo, $request->user()->bibliotecario);

        $mensagens = ['Devolução registrada.'];

        if ($multa = $emprestimo->multa) {
            $mensagens[] = "Atraso de {$multa->diasDeAtraso()} dia(s): multa de "
                .Number::currency($multa->valorTotal(), 'BRL', 'pt_BR').'.';
        }

        if ($emprestimo->exemplar->emprestimoEmCurso?->estaAguardandoRetirada()) {
            $mensagens[] = 'O exemplar foi separado para o próximo leitor da fila.';
        }

        return back()->with('sucesso', implode(' ', $mensagens));
    }
}
