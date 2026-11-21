<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bibliotecario\SalvarEdicaoRequest;
use App\Models\Edicao;
use App\Models\Editora;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF12: edições.
 */
class EdicaoController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('busca'));

        $edicoes = Edicao::query()
            ->with('obra', 'editora')
            ->withCount('exemplares')
            ->when($busca !== '', fn ($consulta) => $consulta
                ->where('isbn', preg_replace('/\D/', '', $busca))
                ->orWhereHas('obra', fn ($obra) => $obra->whereLike('titulo', '%'.$busca.'%')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('bibliotecario.edicoes.index', ['edicoes' => $edicoes, 'busca' => $busca]);
    }

    public function create(Request $request): View
    {
        return $this->formulario(new Edicao(['obra_id' => $request->integer('obra') ?: null]));
    }

    public function store(SalvarEdicaoRequest $request): RedirectResponse
    {
        Edicao::create($request->validated());

        return $this->voltarOu($request, route('bibliotecario.edicoes.index'), 'Edição cadastrada.');
    }

    public function edit(Edicao $edicao): View
    {
        return $this->formulario($edicao);
    }

    public function update(SalvarEdicaoRequest $request, Edicao $edicao): RedirectResponse
    {
        $edicao->update($request->validated());

        return redirect()->route('bibliotecario.edicoes.index')->with('sucesso', 'Edição atualizada.');
    }

    public function destroy(Edicao $edicao): RedirectResponse
    {
        return $this->excluir($edicao, 'bibliotecario.edicoes.index');
    }

    private function formulario(Edicao $edicao): View
    {
        return view('bibliotecario.edicoes.form', [
            'edicao' => $edicao,
            'obras' => Obra::query()->orderBy('titulo')->get(),
            'editoras' => Editora::query()->orderBy('nome')->get(),
        ]);
    }
}
