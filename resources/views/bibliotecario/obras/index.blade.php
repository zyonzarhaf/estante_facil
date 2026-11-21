<x-layout titulo="Obras">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-busca :acao="route('bibliotecario.obras.index')" :valor="$busca" placeholder="Título" />
        <a href="{{ route('bibliotecario.obras.create') }}" class="botao-primario">Cadastrar obra</a>
    </div>

    <div class="cartao overflow-x-auto">
        @if ($obras->isEmpty())
            <x-vazio>Nenhuma obra encontrada.</x-vazio>
        @else
            <table class="tabela">
                <thead><tr><th>Título</th><th>Autores</th><th>Edições</th><th></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($obras as $obra)
                        <tr>
                            <td><a href="{{ route('acervo.show', $obra) }}" class="font-medium text-emerald-800 hover:underline">{{ $obra->titulo }}</a></td>
                            <td>{{ $obra->autores->pluck('nome')->join(', ') }}</td>
                            <td>
                                {{ $obra->edicoes_count }}
                                <x-link-cadastro rota="bibliotecario.edicoes.create" rotulo="edição" :parametros="['obra' => $obra->id]" class="ml-2" />
                            </td>
                            <td>
                                <x-acoes-de-registro
                                    :editar="route('bibliotecario.obras.edit', $obra)"
                                    :excluir="route('bibliotecario.obras.destroy', $obra)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $obras->links() }}</div>
        @endif
    </div>
</x-layout>
