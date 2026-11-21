@props(['emprestimo'])

@if ($emprestimo->estaAtrasado())
    <span class="selo bg-red-100 text-red-800">Atrasado</span>
@elseif ($emprestimo->prazoRetiradaVencido())
    <span class="selo bg-red-100 text-red-800">Prazo de retirada vencido</span>
@else
    <span @class([
        'selo',
        'bg-sky-100 text-sky-800' => $emprestimo->estaAguardandoRetirada(),
        'bg-emerald-100 text-emerald-800' => $emprestimo->estaAtivo(),
        'bg-stone-200 text-stone-700' => ! $emprestimo->estaAtivo() && ! $emprestimo->estaAguardandoRetirada(),
    ])>{{ $emprestimo->situacao->rotulo() }}</span>
@endif
