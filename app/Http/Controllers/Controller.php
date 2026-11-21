<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Depois de um cadastro, volta ao formulário de onde o usuário veio
     * (campo "retorno"), se for uma página desta aplicação.
     */
    protected function voltarOu(Request $request, string $destinoPadrao, string $mensagem): RedirectResponse
    {
        $retorno = (string) $request->input('retorno');
        $destino = str_starts_with($retorno, url('/').'/') ? $retorno : $destinoPadrao;

        return redirect()->to($destino)->with('sucesso', $mensagem);
    }

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
