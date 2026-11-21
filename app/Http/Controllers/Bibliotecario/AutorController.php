<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Requests\Bibliotecario\SalvarNomeRequest;
use App\Models\Autor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AutorController extends CadastroPorNomeController
{
    protected function model(): string
    {
        return Autor::class;
    }

    protected function recurso(): string
    {
        return 'autores';
    }

    protected function rotulos(): array
    {
        return ['singular' => 'autor', 'plural' => 'autores'];
    }

    public function edit(Autor $autor): View
    {
        return $this->formulario($autor);
    }

    public function update(SalvarNomeRequest $request, Autor $autor): RedirectResponse
    {
        return $this->salvar($request, $autor);
    }

    public function destroy(Autor $autor): RedirectResponse
    {
        return $this->excluir($autor, 'bibliotecario.autores.index');
    }
}
