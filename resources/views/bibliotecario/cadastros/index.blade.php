<x-layout :titulo="ucfirst($rotulos['plural'])">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-busca :acao="route('bibliotecario.'.$recurso.'.index')" :valor="$busca" placeholder="Nome" />
        <a href="{{ route('bibliotecario.'.$recurso.'.create') }}" class="botao-primario">Cadastrar {{ $rotulos['singular'] }}</a>
    </div>

    <div class="cartao overflow-x-auto">
        @if ($registros->isEmpty())
            <x-vazio>Nenhum registro encontrado.</x-vazio>
        @else
            <table class="tabela">
                <thead><tr><th>Nome</th><th></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($registros as $registro)
                        <tr>
                            <td>{{ $registro->nome }}</td>
                            <td>
                                <x-acoes-de-registro
                                    :editar="route('bibliotecario.'.$recurso.'.edit', $registro)"
                                    :excluir="route('bibliotecario.'.$recurso.'.destroy', $registro)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $registros->links() }}</div>
        @endif
    </div>
</x-layout>
