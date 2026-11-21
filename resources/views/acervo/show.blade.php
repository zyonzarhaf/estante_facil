@php($ehLeitor = auth()->user()->ehLeitor())

<x-layout :titulo="$obra->titulo">
    <div class="cartao space-y-1 text-sm">
        @if ($obra->subtitulo)
            <p class="text-base text-stone-600">{{ $obra->subtitulo }}</p>
        @endif
        <p><span class="font-medium">Autores:</span> {{ $obra->autores->pluck('nome')->join(', ') }}</p>
        <p><span class="font-medium">Categorias:</span> {{ $obra->categorias->pluck('nome')->join(', ') ?: '—' }}</p>
        @if ($obra->idioma_original)
            <p><span class="font-medium">Idioma original:</span> {{ $obra->idioma_original }}</p>
        @endif
        @if (auth()->user()->ehBibliotecario())
            <p><x-link-cadastro rota="bibliotecario.edicoes.create" rotulo="cadastrar edição desta obra" :parametros="['obra' => $obra->id]" /></p>
        @endif
        @if ($obra->url_acesso)
            <p><span class="font-medium">Acesso online:</span> <a href="{{ $obra->url_acesso }}" class="text-emerald-800 underline" target="_blank" rel="noopener">{{ $obra->url_acesso }}</a></p>
        @endif
    </div>

    @forelse ($obra->edicoes as $edicao)
        <section class="cartao overflow-x-auto">
            <h2 class="mb-2 font-semibold">
                {{ $edicao->numero_edicao }}ª edição · {{ $edicao->editora->nome }}
                <span class="font-normal text-stone-500">· ISBN {{ $edicao->isbn }}{{ $edicao->ano_publicacao ? ' · '.$edicao->ano_publicacao : '' }}</span>
            </h2>
            @if (auth()->user()->ehBibliotecario())
                <x-link-cadastro rota="bibliotecario.exemplares.create" rotulo="cadastrar exemplar desta edição" :parametros="['edicao' => $edicao->id]" class="mb-2" />
            @endif

            @if ($edicao->exemplares->isEmpty())
                <x-vazio>Nenhum exemplar desta edição.</x-vazio>
            @else
                <table class="tabela">
                    <thead>
                        <tr><th>Tombo</th><th>Conservação</th><th>Situação</th>@if ($ehLeitor)<th></th>@endif</tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($edicao->exemplares as $exemplar)
                            @php($situacao = $exemplar->situacaoCirculacao())
                            <tr>
                                <td class="font-mono">{{ $exemplar->codigo_tombo }}</td>
                                <td>{{ $exemplar->estado_conservacao->rotulo() }}</td>
                                <td>
                                    <span @class([
                                        'selo',
                                        'bg-emerald-100 text-emerald-800' => $situacao === \App\Enums\SituacaoCirculacao::Disponivel,
                                        'bg-amber-100 text-amber-800' => $situacao === \App\Enums\SituacaoCirculacao::Emprestado,
                                        'bg-sky-100 text-sky-800' => $situacao === \App\Enums\SituacaoCirculacao::AguardandoRetirada,
                                        'bg-stone-200 text-stone-700' => $situacao === \App\Enums\SituacaoCirculacao::Indisponivel,
                                    ])>{{ $situacao->rotulo() }}</span>
                                </td>
                                @if ($ehLeitor)
                                    <td class="text-right">
                                        @unless ($exemplar->estaIndisponivel())
                                            <form method="POST" action="{{ route('leitor.reservas.store', $exemplar) }}">
                                                @csrf
                                                <button type="submit" class="botao-primario">Reservar</button>
                                            </form>
                                        @endunless
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    @empty
        <div class="cartao">
            <x-vazio>Esta obra não tem edição publicada; não há exemplares para empréstimo.</x-vazio>
        </div>
    @endforelse
</x-layout>
