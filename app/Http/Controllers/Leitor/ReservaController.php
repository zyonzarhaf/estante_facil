<?php

namespace App\Http\Controllers\Leitor;

use App\Http\Controllers\Controller;
use App\Models\Exemplar;
use App\Models\Reserva;
use App\Services\ServicoDeReserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * RF04, RF05: reservar e cancelar reserva.
 */
class ReservaController extends Controller
{
    public function __construct(private ServicoDeReserva $servicoDeReserva) {}

    public function store(Request $request, Exemplar $exemplar): RedirectResponse
    {
        $reserva = $this->servicoDeReserva->reservar($request->user()->leitor, $exemplar);

        $mensagem = $reserva->estaNaFila()
            ? "Reserva feita. Você é o nº {$reserva->posicaoNaFila()} da fila."
            : 'Exemplar separado para você. Retire-o no balcão até '
                .$reserva->emprestimos()->first()->data_limite_retirada->format('d/m/Y').'.';

        return back()->with('sucesso', $mensagem);
    }

    public function destroy(Reserva $reserva): RedirectResponse
    {
        Gate::authorize('cancelar', $reserva);

        $this->servicoDeReserva->cancelar($reserva);

        return back()->with('sucesso', 'Reserva cancelada.');
    }
}
