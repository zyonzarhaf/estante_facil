<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Models\Leitor;
use App\Services\ServicoDeLeitor;
use Illuminate\Http\RedirectResponse;

/**
 * RF14: inativação e reativação de leitores.
 */
class SituacaoDoLeitorController extends Controller
{
    public function __construct(private ServicoDeLeitor $servicoDeLeitor) {}

    public function inativar(Leitor $leitor): RedirectResponse
    {
        $this->servicoDeLeitor->inativar($leitor);

        return back()->with('sucesso', 'Leitor inativado.');
    }

    public function reativar(Leitor $leitor): RedirectResponse
    {
        $this->servicoDeLeitor->reativar($leitor);

        return back()->with('sucesso', 'Leitor reativado.');
    }
}
