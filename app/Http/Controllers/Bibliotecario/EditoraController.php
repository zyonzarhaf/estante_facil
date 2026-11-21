<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Requests\Bibliotecario\SalvarNomeRequest;
use App\Models\Editora;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EditoraController extends CadastroPorNomeController
{
    protected function model(): string
    {
        return Editora::class;
    }

    protected function recurso(): string
    {
        return 'editoras';
    }

    protected function rotulos(): array
    {
        return ['singular' => 'editora', 'plural' => 'editoras'];
    }

    public function edit(Editora $editora): View
    {
        return $this->formulario($editora);
    }

    public function update(SalvarNomeRequest $request, Editora $editora): RedirectResponse
    {
        return $this->salvar($request, $editora);
    }

    public function destroy(Editora $editora): RedirectResponse
    {
        return $this->excluir($editora, 'bibliotecario.editoras.index');
    }
}
