<x-layout titulo="Edições">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-busca :acao="route('bibliotecario.edicoes.index')" :valor="$busca" placeholder="Título ou ISBN" />
        <a href="{{ route('bibliotecario.edicoes.create') }}" class="botao-primario">Cadastrar edição</a>
    </div>

    <div class="cartao overflow-x-auto">
        @if ($edicoes->isEmpty())
            <x-vazio>Nenhuma edição encontrada.</x-vazio>
        @else
            <table class="tabela">
                <thead><tr><th>Obra</th><th>Edição</th><th>Editora</th><th>ISBN</th><th>Exemplares</th><th></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($edicoes as $edicao)
                        <tr>
                            <td>{{ $edicao->obra->titulo }}</td>
                            <td>{{ $edicao->numero_edicao }}ª{{ $edicao->ano_publicacao ? ' ('.$edicao->ano_publicacao.')' : '' }}</td>
                            <td>{{ $edicao->editora->nome }}</td>
                            <td class="font-mono">{{ $edicao->isbn }}</td>
                            <td>
                                {{ $edicao->exemplares_count }}
                                <a href="{{ route('bibliotecario.exemplares.create', ['edicao' => $edicao->id]) }}" class="ml-2 text-xs text-emerald-800 hover:underline">+ exemplar</a>
                            </td>
                            <td>
                                <x-acoes-de-registro
                                    :editar="route('bibliotecario.edicoes.edit', $edicao)"
                                    :excluir="route('bibliotecario.edicoes.destroy', $edicao)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $edicoes->links() }}</div>
        @endif
    </div>
</x-layout>
