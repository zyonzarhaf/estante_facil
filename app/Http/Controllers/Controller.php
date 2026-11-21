<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    /**
     * Exclui o registro ou, se ele estiver em uso (FK), explica por que não excluiu.
     */
    protected function excluir(Model $registro, string $rotaDaListagem): RedirectResponse
    {
        try {
            $registro->delete();
        } catch (QueryException $excecao) {
            if ($excecao->getCode() === '23503') {
                return back()->with('erro', 'Não é possível excluir: o registro está em uso.');
            }

            throw $excecao;
        }

        return redirect()->route($rotaDaListagem)->with('sucesso', 'Registro excluído.');
    }
}
