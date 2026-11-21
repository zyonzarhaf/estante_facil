@php($usuario = $leitor->usuario)
@php($endereco = $usuario->endereco)

<x-layout :titulo="$usuario->nome">
    @if ($bloqueado)
        <div role="alert" class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Leitor bloqueado: há multa pendente, empréstimo atrasado ou o cadastro está inativo.
        </div>
    @endif

    <section class="cartao flex flex-wrap items-start justify-between gap-4 text-sm">
        <div class="space-y-1">
            <p><span class="font-medium">CPF:</span> <span class="font-mono">{{ $usuario->cpf }}</span></p>
            <p><span class="font-medium">E-mail:</span> {{ $usuario->email }}</p>
            <p><span class="font-medium">Telefone:</span> {{ $usuario->telefone ?: '—' }}</p>
            <p><span class="font-medium">Endereço:</span>
                {{ $endereco->logradouro }}, {{ $endereco->numero }}{{ $endereco->complemento ? ' — '.$endereco->complemento : '' }},
                {{ $endereco->bairro }}, {{ $endereco->cidade }}/{{ $endereco->uf }}, CEP {{ $endereco->cep }}</p>
            <p><span class="font-medium">Cadastro:</span> {{ $usuario->data_cadastro->format('d/m/Y') }} · {{ $usuario->estaAtivo() ? 'Ativo' : 'Inativo' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bibliotecario.leitores.edit', $leitor) }}" class="botao-secundario">Editar</a>
            <form method="POST" action="{{ $usuario->estaAtivo() ? route('bibliotecario.leitores.inativacao', $leitor) : route('bibliotecario.leitores.reativacao', $leitor) }}">
                @csrf
                <button type="submit" class="{{ $usuario->estaAtivo() ? 'botao-perigo' : 'botao-primario' }}">
                    {{ $usuario->estaAtivo() ? 'Inativar' : 'Reativar' }}
                </button>
            </form>
        </div>
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Empréstimos</h2>
        @if ($emprestimos->isEmpty())
            <x-vazio>Nenhum empréstimo.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Obra</th><th>Situação</th><th>Retirada</th><th>Prevista</th><th>Devolução</th><th>Renovações</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($emprestimos as $emprestimo)
                        <tr>
                            <td><x-obra-do-exemplar :exemplar="$emprestimo->exemplar" /></td>
                            <td><x-situacao-emprestimo :emprestimo="$emprestimo" /></td>
                            <td>{{ $emprestimo->data_retirada?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $emprestimo->data_prevista_devolucao?->format('d/m/Y') ?? 'Retirar até '.$emprestimo->data_limite_retirada?->format('d/m/Y') }}</td>
                            <td>{{ $emprestimo->data_devolucao?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $emprestimo->renovacoes->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Reservas</h2>
        @if ($reservas->isEmpty())
            <x-vazio>Nenhuma reserva.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Obra</th><th>Reservado em</th><th>Situação</th><th>Posição</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($reservas as $reserva)
                        <tr>
                            <td><x-obra-do-exemplar :exemplar="$reserva->exemplar" /></td>
                            <td>{{ $reserva->data_hora_reserva->format('d/m/Y H:i') }}</td>
                            <td>{{ $reserva->situacao->rotulo() }}</td>
                            <td>{{ $reserva->posicaoNaFila() ? $reserva->posicaoNaFila().'º' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Multas</h2>
        <x-multas :multas="$multas" />
    </section>
</x-layout>
