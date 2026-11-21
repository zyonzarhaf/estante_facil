@props(['multas', 'comLeitor' => false])

@if ($multas->isEmpty())
    <x-vazio>Nenhuma multa.</x-vazio>
@else
    <table class="tabela">
        <thead>
            <tr><th>Obra</th><th>Devolução</th><th>Dias de atraso</th><th>Valor</th><th>Situação</th></tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach ($multas as $multa)
                <tr>
                    <td><x-obra-do-exemplar :exemplar="$multa->emprestimo->exemplar" /></td>
                    <td>{{ $multa->emprestimo->data_devolucao->format('d/m/Y') }}</td>
                    <td>{{ $multa->diasDeAtraso() }}</td>
                    <td>{{ Number::currency($multa->valorTotal(), 'BRL', 'pt_BR') }}</td>
                    <td>
                        <span @class(['selo', 'bg-red-100 text-red-800' => $multa->estaPendente(), 'bg-stone-200 text-stone-700' => ! $multa->estaPendente()])>
                            {{ $multa->situacao()->rotulo() }}{{ $multa->estaPendente() ? '' : ' em '.$multa->data_pagamento->format('d/m/Y') }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
