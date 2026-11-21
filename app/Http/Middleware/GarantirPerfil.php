<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RNF11: restringe a rota ao perfil informado (LEITOR ou BIBLIOTECARIO).
 */
class GarantirPerfil
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $perfil): Response
    {
        abort_unless($request->user()?->perfil?->value === $perfil, Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
