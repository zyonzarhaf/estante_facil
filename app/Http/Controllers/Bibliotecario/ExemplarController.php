<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Enums\EstadoConservacao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bibliotecario\SalvarExemplarRequest;
use App\Models\Edicao;
use App\Models\Exemplar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF12: exemplares. A indisponibilidade (RF13) fica em
 * DisponibilidadeDoExemplarController, pois tem efeitos em outros agregados.
 */
class ExemplarController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('busca'));

        $exemplares = Exemplar::query()
            ->with('edicao.obra', 'emprestimoEmCurso')
            ->when($busca !== '', fn ($consulta) => $consulta
                ->whereLike('codigo_tombo', '%'.$busca.'%')
                ->orWhereHas('edicao.obra', fn ($obra) => $obra->whereLike('titulo', '%'.$busca.'%')))
            ->orderBy('codigo_tombo')
            ->paginate(20)
            ->withQueryString();

        return view('bibliotecario.exemplares.index', ['exemplares' => $exemplares, 'busca' => $busca]);
    }

    public function create(Request $request): View
    {
        return $this->formulario(new Exemplar([
            'edicao_id' => $request->integer('edicao') ?: null,
            'data_aquisicao' => today(),
            'estado_conservacao' => EstadoConservacao::Novo,
        ]));
    }

    public function store(SalvarExemplarRequest $request): RedirectResponse
    {
        Exemplar::create($request->validated());

        return $this->voltarOu($request, route('bibliotecario.exemplares.index'), 'Exemplar cadastrado.');
    }

    public function edit(Exemplar $exemplar): View
    {
        return $this->formulario($exemplar);
    }

    public function update(SalvarExemplarRequest $request, Exemplar $exemplar): RedirectResponse
    {
        $exemplar->update($request->validated());

        return redirect()->route('bibliotecario.exemplares.index')->with('sucesso', 'Exemplar atualizado.');
    }

    public function destroy(Exemplar $exemplar): RedirectResponse
    {
        return $this->excluir($exemplar, 'bibliotecario.exemplares.index');
    }

    private function formulario(Exemplar $exemplar): View
    {
        return view('bibliotecario.exemplares.form', [
            'exemplar' => $exemplar,
            'edicoes' => Edicao::query()->with('obra', 'editora')->get()->sortBy('obra.titulo'),
            'estados' => EstadoConservacao::cases(),
        ]);
    }
}
