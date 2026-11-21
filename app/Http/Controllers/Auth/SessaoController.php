<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntrarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * RF01: entrar com e-mail e senha ("lembrar de mim") e sair.
 */
class SessaoController extends Controller
{
    public function create(): View
    {
        return view('auth.entrar');
    }

    public function store(EntrarRequest $request): RedirectResponse
    {
        $credenciais = ['email' => $request->validated('email'), 'password' => $request->validated('senha')];

        if (! Auth::attempt($credenciais, $request->boolean('lembrar'))) {
            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('inicio'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
