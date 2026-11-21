<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InicioController extends Controller
{
    /**
     * Encaminha para o painel do perfil do usuário.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        return match (true) {
            $usuario === null => redirect()->route('login'),
            $usuario->ehBibliotecario() => redirect()->route('bibliotecario.painel'),
            default => redirect()->route('leitor.painel'),
        };
    }
}
