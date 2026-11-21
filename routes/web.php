<?php

use App\Http\Controllers\AcervoController;
use App\Http\Controllers\Auth\SessaoController;
use App\Http\Controllers\Bibliotecario;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\Leitor;
use Illuminate\Support\Facades\Route;

Route::get('/', InicioController::class)->name('inicio');

// RF01: entrar e sair
Route::middleware('guest')->group(function () {
    Route::get('/entrar', [SessaoController::class, 'create'])->name('login');
    Route::post('/entrar', [SessaoController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/sair', [SessaoController::class, 'destroy'])->name('logout');

    // RF02, RF03: consulta ao acervo (ambos os perfis)
    Route::get('/acervo', [AcervoController::class, 'index'])->name('acervo.index');
    Route::get('/acervo/obras/{obra}', [AcervoController::class, 'show'])->name('acervo.show');

    // Funções do leitor
    Route::middleware('perfil:LEITOR')->prefix('leitor')->name('leitor.')->group(function () {
        Route::get('/', Leitor\PainelController::class)->name('painel');
        Route::post('/exemplares/{exemplar}/reservas', [Leitor\ReservaController::class, 'store'])->name('reservas.store');
        Route::delete('/reservas/{reserva}', [Leitor\ReservaController::class, 'destroy'])->name('reservas.destroy');
        Route::post('/emprestimos/{emprestimo}/renovacao', Leitor\RenovacaoController::class)->name('emprestimos.renovacao');
    });

    // Funções do bibliotecário
    Route::middleware('perfil:BIBLIOTECARIO')->prefix('bibliotecario')->name('bibliotecario.')->group(function () {
        Route::get('/', Bibliotecario\PainelController::class)->name('painel');

        // Atendimentos (RF08–RF11)
        Route::post('/emprestimos', Bibliotecario\EmprestimoController::class)->name('emprestimos.store');
        Route::post('/emprestimos/{emprestimo}/retirada', Bibliotecario\RetiradaController::class)->name('emprestimos.retirada');
        Route::post('/emprestimos/{emprestimo}/devolucao', Bibliotecario\DevolucaoController::class)->name('emprestimos.devolucao');
        Route::post('/emprestimos/{emprestimo}/multa/pagamento', Bibliotecario\PagamentoDeMultaController::class)->name('emprestimos.multa.pagamento');

        // Leitores (RF14, RF15)
        Route::resource('leitores', Bibliotecario\LeitorController::class)
            ->parameters(['leitores' => 'leitor'])
            ->except('destroy');
        Route::post('/leitores/{leitor}/inativacao', [Bibliotecario\SituacaoDoLeitorController::class, 'inativar'])->name('leitores.inativacao');
        Route::post('/leitores/{leitor}/reativacao', [Bibliotecario\SituacaoDoLeitorController::class, 'reativar'])->name('leitores.reativacao');

        // Acervo (RF12, RF13)
        Route::resource('obras', Bibliotecario\ObraController::class)->except('show');
        Route::resource('autores', Bibliotecario\AutorController::class)
            ->parameters(['autores' => 'autor'])
            ->except('show');
        Route::resource('categorias', Bibliotecario\CategoriaController::class)->except('show');
        Route::resource('editoras', Bibliotecario\EditoraController::class)->except('show');
        Route::resource('edicoes', Bibliotecario\EdicaoController::class)
            ->parameters(['edicoes' => 'edicao'])
            ->except('show');
        Route::resource('exemplares', Bibliotecario\ExemplarController::class)
            ->parameters(['exemplares' => 'exemplar'])
            ->except('show');
        Route::post('/exemplares/{exemplar}/indisponibilidade', [Bibliotecario\DisponibilidadeDoExemplarController::class, 'marcarIndisponivel'])->name('exemplares.indisponibilidade');
        Route::delete('/exemplares/{exemplar}/indisponibilidade', [Bibliotecario\DisponibilidadeDoExemplarController::class, 'marcarDisponivel'])->name('exemplares.disponibilidade');
    });
});
