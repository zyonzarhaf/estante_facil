<x-layout titulo="Exemplares">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-busca :acao="route('bibliotecario.exemplares.index')" :valor="$busca" placeholder="Tombo ou título" />
        <a href="{{ route('bibliotecario.exemplares.create') }}" class="botao-primario">Cadastrar exemplar</a>
    </div>

    <div class="cartao overflow-x-auto">
        @if ($exemplares->isEmpty())
            <x-vazio>Nenhum exemplar encontrado.</x-vazio>
        @else
            <table class="tabela">
                <thead><tr><th>Tombo</th><th>Obra</th><th>Conservação</th><th>Situação</th><th></th></tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($exemplares as $exemplar)
                        <tr>
                            <td class="font-mono">{{ $exemplar->codigo_tombo }}</td>
                            <td>{{ $exemplar->edicao->obra->titulo }} <span class="text-stone-500">({{ $exemplar->edicao->numero_edicao }}ª ed.)</span></td>
                            <td>{{ $exemplar->estado_conservacao->rotulo() }}</td>
                            <td>{{ $exemplar->situacaoCirculacao()->rotulo() }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route($exemplar->estaIndisponivel() ? 'bibliotecario.exemplares.disponibilidade' : 'bibliotecario.exemplares.indisponibilidade', $exemplar) }}"
                                          @unless ($exemplar->estaIndisponivel()) onsubmit="return confirm('As reservas na fila serão canceladas e uma retirada pendente será encerrada. Continuar?')" @endunless>
                                        @csrf
                                        @if ($exemplar->estaIndisponivel())
                                            @method('DELETE')
                                        @endif
                                        <button type="submit" class="botao-secundario">{{ $exemplar->estaIndisponivel() ? 'Disponibilizar' : 'Indisponibilizar' }}</button>
                                    </form>
                                    <x-acoes-de-registro
                                        :editar="route('bibliotecario.exemplares.edit', $exemplar)"
                                        :excluir="route('bibliotecario.exemplares.destroy', $exemplar)" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $exemplares->links() }}</div>
        @endif
    </div>
</x-layout>
