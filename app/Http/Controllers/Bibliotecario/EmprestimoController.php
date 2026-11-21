<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bibliotecario\EmprestarExemplarRequest;
use App\Models\Exemplar;
use App\Models\Usuario;
use App\Services\ServicoDeEmprestimo;
use Illuminate\Http\RedirectResponse;

/**
 * RF08: empréstimo direto no balcão.
 */
class EmprestimoController extends Controller
{
    public function __construct(private ServicoDeEmprestimo $servicoDeEmprestimo) {}

    public function __invoke(EmprestarExemplarRequest $request): RedirectResponse
    {
        $leitor = Usuario::query()->where('cpf', $request->validated('cpf'))->firstOrFail()->leitor;

        if ($leitor === null) {
            return back()->withInput()->withErrors(['cpf' => 'Este CPF não pertence a um leitor.']);
        }

        $exemplar = Exemplar::query()->where('codigo_tombo', $request->validated('codigo_tombo'))->firstOrFail();

        $emprestimo = $this->servicoDeEmprestimo->emprestar($leitor, $exemplar, $request->user()->bibliotecario);

        return back()->with('sucesso', 'Empréstimo registrado. Devolução até '
            .$emprestimo->data_prevista_devolucao->format('d/m/Y').'.');
    }
}
