@props(['exemplar'])

<a href="{{ route('acervo.show', $exemplar->edicao->obra) }}" class="font-medium text-emerald-800 hover:underline">{{ $exemplar->edicao->obra->titulo }}</a>
<span class="block font-mono text-xs text-stone-500">{{ $exemplar->codigo_tombo }}</span>
