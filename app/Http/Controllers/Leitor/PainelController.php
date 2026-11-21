<?php

namespace App\Http\Controllers\Leitor;

use App\Enums\SituacaoEmprestimo;
use App\Http\Controllers\Controller;
use App\Models\Multa;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF07: empréstimos, reservas (com posição na fila) e multas do leitor.
 */
class PainelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $leitor = $request->user()->leitor;
        $comObra = ['exemplar.edicao.obra'];

        return view('leitor.painel', [
            'leitor' => $leitor,
            'bloqueado' => $leitor->estaBloqueado(),
            'emprestimosEmCurso' => $leitor->emprestimos()->emCurso()->with([...$comObra, 'renovacoes'])->orderBy('id')->get(),
            'reservasNaFila' => $leitor->reservas()->naFila()->with($comObra)->orderBy('data_hora_reserva')->get(),
            'historico' => $leitor->emprestimos()->where('situacao', SituacaoEmprestimo::Encerrado)
                ->with($comObra)->latest('id')->limit(20)->get(),
            'multas' => Multa::query()
                ->whereHas('emprestimo', fn ($consulta) => $consulta->where('leitor_id', $leitor->usuario_id))
                ->with('emprestimo.exemplar.edicao.obra')
                ->latest('criado_em')
                ->get(),
        ]);
    }
}
