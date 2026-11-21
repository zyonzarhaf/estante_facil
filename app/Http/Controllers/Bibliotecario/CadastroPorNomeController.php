<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bibliotecario\SalvarNomeRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD dos cadastros que têm só o nome (autores, categorias, editoras).
 * Sem regra de negócio: o controller usa o model diretamente.
 */
abstract class CadastroPorNomeController extends Controller
{
    /**
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    /**
     * Nome do recurso nas rotas (ex.: "autores").
     */
    abstract protected function recurso(): string;

    /**
     * @return array{singular: string, plural: string}
     */
    abstract protected function rotulos(): array;

    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('busca'));

        $registros = $this->model()::query()
            ->when($busca !== '', fn ($consulta) => $consulta->whereLike('nome', '%'.$busca.'%'))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('bibliotecario.cadastros.index', [
            'registros' => $registros,
            'busca' => $busca,
            'recurso' => $this->recurso(),
            'rotulos' => $this->rotulos(),
        ]);
    }

    public function create(): View
    {
        return $this->formulario(null);
    }

    public function store(SalvarNomeRequest $request): RedirectResponse
    {
        $this->model()::create($request->validated());

        return redirect()->route("bibliotecario.{$this->recurso()}.index")
            ->with('sucesso', ucfirst($this->rotulos()['singular']).' cadastrado(a).');
    }

    protected function formulario(?Model $registro): View
    {
        return view('bibliotecario.cadastros.form', [
            'registro' => $registro,
            'recurso' => $this->recurso(),
            'rotulos' => $this->rotulos(),
        ]);
    }

    protected function salvar(SalvarNomeRequest $request, Model $registro): RedirectResponse
    {
        $registro->update($request->validated());

        return redirect()->route("bibliotecario.{$this->recurso()}.index")
            ->with('sucesso', ucfirst($this->rotulos()['singular']).' atualizado(a).');
    }
}
