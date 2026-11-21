<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF02, RF03: consulta ao acervo, para leitores e bibliotecários.
 */
class AcervoController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->query('busca'));

        $obras = Obra::query()
            ->with('autores', 'categorias')
            ->when($busca !== '', function (Builder $consulta) use ($busca) {
                $termo = '%'.$busca.'%';
                $consulta->where(fn (Builder $ou) => $ou
                    ->whereLike('titulo', $termo)
                    ->orWhereHas('autores', fn (Builder $autor) => $autor->whereLike('nome', $termo))
                    ->orWhereHas('categorias', fn (Builder $categoria) => $categoria->whereLike('nome', $termo))
                    ->orWhereHas('edicoes.editora', fn (Builder $editora) => $editora->whereLike('nome', $termo)));
            })
            ->orderBy('titulo')
            ->paginate(15)
            ->withQueryString();

        return view('acervo.index', ['obras' => $obras, 'busca' => $busca]);
    }

    public function show(Obra $obra): View
    {
        $obra->load('autores', 'categorias', 'edicoes.editora', 'edicoes.exemplares.emprestimoEmCurso');

        return view('acervo.show', ['obra' => $obra]);
    }
}
