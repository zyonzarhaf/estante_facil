<x-layout titulo="Leitores">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-busca :acao="route('bibliotecario.leitores.index')" :valor="$busca" placeholder="Nome ou CPF" />
        <a href="{{ route('bibliotecario.leitores.create') }}" class="botao-primario">Cadastrar leitor</a>
    </div>

    <div class="cartao overflow-x-auto">
        @if ($leitores->isEmpty())
            <x-vazio>Nenhum leitor encontrado.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Nome</th><th>CPF</th><th>E-mail</th><th>Situação</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($leitores as $leitor)
                        <tr>
                            <td><a href="{{ route('bibliotecario.leitores.show', $leitor) }}" class="font-medium text-emerald-800 hover:underline">{{ $leitor->usuario->nome }}</a></td>
                            <td class="font-mono">{{ $leitor->usuario->cpf }}</td>
                            <td>{{ $leitor->usuario->email }}</td>
                            <td>{{ $leitor->usuario->estaAtivo() ? 'Ativo' : 'Inativo' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $leitores->links() }}</div>
        @endif
    </div>
</x-layout>
