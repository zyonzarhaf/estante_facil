<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Requests\Bibliotecario\SalvarNomeRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends CadastroPorNomeController
{
    protected function model(): string
    {
        return Categoria::class;
    }

    protected function recurso(): string
    {
        return 'categorias';
    }

    protected function rotulos(): array
    {
        return ['singular' => 'categoria', 'plural' => 'categorias'];
    }

    public function edit(Categoria $categoria): View
    {
        return $this->formulario($categoria);
    }

    public function update(SalvarNomeRequest $request, Categoria $categoria): RedirectResponse
    {
        return $this->salvar($request, $categoria);
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        return $this->excluir($categoria, 'bibliotecario.categorias.index');
    }
}
