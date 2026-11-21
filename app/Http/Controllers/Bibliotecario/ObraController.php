<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bibliotecario\SalvarObraRequest;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * RF12: obras, sempre com pelo menos um autor (gravados na mesma transação).
 */
class ObraController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('busca'));

        $obras = Obra::query()
            ->with('autores')
            ->withCount('edicoes')
            ->when($busca !== '', fn ($consulta) => $consulta->whereLike('titulo', '%'.$busca.'%'))
            ->orderBy('titulo')
            ->paginate(20)
            ->withQueryString();

        return view('bibliotecario.obras.index', ['obras' => $obras, 'busca' => $busca]);
    }

    public function create(): View
    {
        return $this->formulario(new Obra);
    }

    public function store(SalvarObraRequest $request): RedirectResponse
    {
        DB::transaction(fn () => $this->gravar(new Obra, $request->validated()));

        return redirect()->route('bibliotecario.obras.index')->with('sucesso', 'Obra cadastrada.');
    }

    public function edit(Obra $obra): View
    {
        return $this->formulario($obra->load('autores', 'categorias'));
    }

    public function update(SalvarObraRequest $request, Obra $obra): RedirectResponse
    {
        DB::transaction(fn () => $this->gravar($obra, $request->validated()));

        return redirect()->route('bibliotecario.obras.index')->with('sucesso', 'Obra atualizada.');
    }

    public function destroy(Obra $obra): RedirectResponse
    {
        return $this->excluir($obra, 'bibliotecario.obras.index');
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function gravar(Obra $obra, array $dados): void
    {
        $obra->fill(Arr::except($dados, ['autores', 'categorias']))->save();
        $obra->autores()->sync($dados['autores']);
        $obra->categorias()->sync($dados['categorias'] ?? []);
    }

    private function formulario(Obra $obra): View
    {
        return view('bibliotecario.obras.form', [
            'obra' => $obra,
            'autores' => Autor::query()->orderBy('nome')->get(),
            'categorias' => Categoria::query()->orderBy('nome')->get(),
        ]);
    }
}
