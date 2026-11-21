<?php

namespace App\Http\Controllers\Bibliotecario;

use App\Http\Controllers\Controller;
use App\Models\Exemplar;
use App\Services\ServicoDeExemplar;
use Illuminate\Http\RedirectResponse;

/**
 * RF13: marcar exemplar como indisponível ou disponível (R14).
 */
class DisponibilidadeDoExemplarController extends Controller
{
    public function __construct(private ServicoDeExemplar $servicoDeExemplar) {}

    public function marcarIndisponivel(Exemplar $exemplar): RedirectResponse
    {
        $this->servicoDeExemplar->marcarIndisponivel($exemplar);

        return back()->with('sucesso', 'Exemplar marcado como indisponível; reservas na fila foram canceladas.');
    }

    public function marcarDisponivel(Exemplar $exemplar): RedirectResponse
    {
        $this->servicoDeExemplar->marcarDisponivel($exemplar);

        return back()->with('sucesso', 'Exemplar disponível novamente.');
    }
}
