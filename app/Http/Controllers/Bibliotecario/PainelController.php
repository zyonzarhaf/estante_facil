<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Models\Emprestimo;
use Illuminate\View\View;

/**
 * Atendimentos do balcão: retiradas pendentes, empréstimos ativos e multas.
 */
class PainelController extends Controller
{
    public function __invoke(): View
    {
        $comDetalhes = ['leitor.usuario', 'exemplar.edicao.obra'];

        return view('bibliotecario.painel', [
            'retiradasPendentes' => Emprestimo::query()
                ->where('situacao', 'AGUARDANDO_RETIRADA')
                ->with($comDetalhes)
                ->orderBy('data_limite_retirada')
                ->get(),
            'emprestimosAtivos' => Emprestimo::query()
                ->ativos()
                ->with($comDetalhes)
                ->orderBy('data_prevista_devolucao')
                ->get(),
            'emprestimosComMultaPendente' => Emprestimo::query()
                ->whereHas('multa', fn ($multa) => $multa->pendentes())
                ->with([...$comDetalhes, 'multa'])
                ->orderBy('data_devolucao')
                ->get(),
        ]);
    }
}
