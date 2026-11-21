<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavegacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_leitor_entra_com_email_e_senha_e_vai_para_o_proprio_painel(): void
    {
        $leitor = $this->criarLeitor();

        $this->post(route('login.store'), ['email' => $leitor->usuario->email, 'senha' => 'senha-secreta'])
            ->assertRedirect(route('inicio'));

        $this->get(route('inicio'))->assertRedirect(route('leitor.painel'));
        $this->get(route('leitor.painel'))->assertOk()->assertSee('Minha conta');
    }

    public function test_senha_errada_e_recusada_com_mensagem_em_portugues(): void
    {
        $leitor = $this->criarLeitor();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $leitor->usuario->email, 'senha' => 'errada'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);
    }

    public function test_leitor_nao_acessa_funcoes_do_bibliotecario(): void
    {
        $this->actingAs($this->criarLeitor()->usuario)
            ->get(route('bibliotecario.painel'))
            ->assertForbidden();
    }

    public function test_recusa_volta_com_o_motivo(): void
    {
        $exemplar = $this->criarExemplar();
        $exemplar->marcarIndisponivel();

        $this->actingAs($this->criarLeitor()->usuario)
            ->from(route('acervo.show', $exemplar->edicao->obra))
            ->post(route('leitor.reservas.store', $exemplar))
            ->assertRedirect(route('acervo.show', $exemplar->edicao->obra))
            ->assertSessionHas('erro', 'O exemplar está indisponível.');
    }

    public function test_paginas_principais_renderizam(): void
    {
        $exemplar = $this->criarExemplar();
        $leitor = $this->criarLeitor();
        $this->emprestimoAtivo($leitor, $exemplar, diasAtras: 7);

        $this->actingAs($leitor->usuario)->get(route('acervo.index'))->assertOk();
        $this->actingAs($leitor->usuario)->get(route('acervo.show', $exemplar->edicao->obra))->assertOk()->assertSee($exemplar->codigo_tombo);
        $this->actingAs($leitor->usuario)->get(route('leitor.painel'))->assertOk()->assertSee('Atrasado');

        $bibliotecario = $this->criarBibliotecario()->usuario;
        foreach (['painel', 'leitores.index', 'leitores.create', 'obras.index', 'obras.create', 'edicoes.index', 'edicoes.create',
            'exemplares.index', 'exemplares.create', 'autores.index', 'categorias.index', 'editoras.index'] as $rota) {
            $this->actingAs($bibliotecario)->get(route('bibliotecario.'.$rota))->assertOk();
        }
        $this->actingAs($bibliotecario)->get(route('bibliotecario.leitores.show', $leitor))->assertOk();
    }

    public function test_cadastro_pelo_link_volta_ao_formulario_de_origem(): void
    {
        $bibliotecario = $this->criarBibliotecario()->usuario;
        $formularioDeObra = route('bibliotecario.obras.create');

        $this->actingAs($bibliotecario)
            ->get(route('bibliotecario.obras.create'))
            ->assertSee(route('bibliotecario.autores.create', ['retorno' => $formularioDeObra]), escape: false);

        $this->actingAs($bibliotecario)
            ->post(route('bibliotecario.autores.store'), ['nome' => 'Clarice Lispector', 'retorno' => $formularioDeObra])
            ->assertRedirect($formularioDeObra);

        $this->actingAs($bibliotecario)
            ->post(route('bibliotecario.autores.store'), ['nome' => 'Cecília Meireles', 'retorno' => 'https://externo.exemplo.com/'])
            ->assertRedirect(route('bibliotecario.autores.index'));
    }
}
