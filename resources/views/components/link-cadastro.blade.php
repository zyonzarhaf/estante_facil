@props(['rota', 'rotulo', 'parametros' => []])

{{-- Cadastra uma entidade relacionada e volta para esta página. --}}
<a href="{{ route($rota, [...$parametros, 'retorno' => url()->full()]) }}"
   {{ $attributes->merge(['class' => 'mt-1 inline-block text-xs text-emerald-800 hover:underline']) }}>+ {{ $rotulo }}</a>
