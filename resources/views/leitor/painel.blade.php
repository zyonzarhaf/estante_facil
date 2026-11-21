<x-layout titulo="Minha conta">
    @if ($bloqueado)
        <div role="alert" class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Você está bloqueado para novos empréstimos, retiradas, renovações e reservas: há multa pendente, empréstimo atrasado ou o cadastro está inativo.
        </div>
    @endif

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Empréstimos em curso</h2>
        @if ($emprestimosEmCurso->isEmpty())
            <x-vazio>Nenhum empréstimo em curso.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Obra</th><th>Situação</th><th>Prazo</th><th>Renovações</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($emprestimosEmCurso as $emprestimo)
                        <tr>
                            <td><x-obra-do-exemplar :exemplar="$emprestimo->exemplar" /></td>
                            <td><x-situacao-emprestimo :emprestimo="$emprestimo" /></td>
                            <td>
                                @if ($emprestimo->estaAguardandoRetirada())
                                    Retirar até {{ $emprestimo->data_limite_retirada->format('d/m/Y') }}
                                @else
                                    Devolver até {{ $emprestimo->data_prevista_devolucao->format('d/m/Y') }}
                                @endif
                            </td>
                            <td>{{ $emprestimo->renovacoes->count() }}/{{ config('biblioteca.max_renovacoes') }}</td>
                            <td class="text-right">
                                @if ($emprestimo->estaAtivo())
                                    <form method="POST" action="{{ route('leitor.emprestimos.renovacao', $emprestimo) }}">
                                        @csrf
                                        <button type="submit" class="botao-secundario">Renovar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Reservas na fila</h2>
        @if ($reservasNaFila->isEmpty())
            <x-vazio>Nenhuma reserva na fila.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Obra</th><th>Reservado em</th><th>Posição</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($reservasNaFila as $reserva)
                        <tr>
                            <td><x-obra-do-exemplar :exemplar="$reserva->exemplar" /></td>
                            <td>{{ $reserva->data_hora_reserva->format('d/m/Y H:i') }}</td>
                            <td>{{ $reserva->posicaoNaFila() }}º</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('leitor.reservas.destroy', $reserva) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="botao-secundario">Cancelar</button>
                                </form>
                            </td>
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

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Histórico recente</h2>
        @if ($historico->isEmpty())
            <x-vazio>Nenhum empréstimo encerrado.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Obra</th><th>Retirada</th><th>Devolução</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($historico as $emprestimo)
                        <tr>
                            <td><x-obra-do-exemplar :exemplar="$emprestimo->exemplar" /></td>
                            <td>{{ $emprestimo->data_retirada?->format('d/m/Y') ?? 'Não retirado' }}</td>
                            <td>{{ $emprestimo->data_devolucao?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-layout>
