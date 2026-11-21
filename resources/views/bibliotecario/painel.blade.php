<x-layout titulo="Atendimento">
    <section class="cartao">
        <h2 class="mb-3 text-lg font-semibold">Empréstimo direto</h2>
        <form method="POST" action="{{ route('bibliotecario.emprestimos.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <x-campo nome="cpf" rotulo="CPF do leitor" required inputmode="numeric" class="w-48" />
            <x-campo nome="codigo_tombo" rotulo="Código de tombo" required class="w-48" />
            <button type="submit" class="botao-primario">Emprestar</button>
        </form>
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Retiradas pendentes</h2>
        @if ($retiradasPendentes->isEmpty())
            <x-vazio>Nenhum exemplar aguardando retirada.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Leitor</th><th>Obra</th><th>Retirar até</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($retiradasPendentes as $emprestimo)
                        <tr>
                            <td><a href="{{ route('bibliotecario.leitores.show', $emprestimo->leitor) }}" class="hover:underline">{{ $emprestimo->leitor->usuario->nome }}</a></td>
                            <td><x-obra-do-exemplar :exemplar="$emprestimo->exemplar" /></td>
                            <td>
                                {{ $emprestimo->data_limite_retirada->format('d/m/Y') }}
                                @if ($emprestimo->prazoRetiradaVencido())
                                    <span class="selo bg-red-100 text-red-800">Vencido</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('bibliotecario.emprestimos.retirada', $emprestimo) }}">
                                    @csrf
                                    <button type="submit" class="botao-primario">Registrar retirada</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Empréstimos ativos</h2>
        @if ($emprestimosAtivos->isEmpty())
            <x-vazio>Nenhum empréstimo ativo.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Leitor</th><th>Obra</th><th>Devolver até</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($emprestimosAtivos as $emprestimo)
                        <tr>
                            <td><a href="{{ route('bibliotecario.leitores.show', $emprestimo->leitor) }}" class="hover:underline">{{ $emprestimo->leitor->usuario->nome }}</a></td>
                            <td><x-obra-do-exemplar :exemplar="$emprestimo->exemplar" /></td>
                            <td>
                                {{ $emprestimo->data_prevista_devolucao->format('d/m/Y') }}
                                @if ($emprestimo->estaAtrasado())
                                    <span class="selo bg-red-100 text-red-800">{{ $emprestimo->diasDeAtraso() }} dia(s) de atraso</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('bibliotecario.emprestimos.devolucao', $emprestimo) }}">
                                    @csrf
                                    <button type="submit" class="botao-primario">Registrar devolução</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="cartao overflow-x-auto">
        <h2 class="mb-2 text-lg font-semibold">Multas pendentes</h2>
        @if ($emprestimosComMultaPendente->isEmpty())
            <x-vazio>Nenhuma multa pendente.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Leitor</th><th>Obra</th><th>Dias de atraso</th><th>Valor</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($emprestimosComMultaPendente as $emprestimo)
                        <tr>
                            <td><a href="{{ route('bibliotecario.leitores.show', $emprestimo->leitor) }}" class="hover:underline">{{ $emprestimo->leitor->usuario->nome }}</a></td>
                            <td><x-obra-do-exemplar :exemplar="$emprestimo->exemplar" /></td>
                            <td>{{ $emprestimo->multa->diasDeAtraso() }}</td>
                            <td>{{ Number::currency($emprestimo->multa->valorTotal(), 'BRL', 'pt_BR') }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('bibliotecario.emprestimos.multa.pagamento', $emprestimo) }}">
                                    @csrf
                                    <button type="submit" class="botao-primario">Registrar pagamento</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-layout>
