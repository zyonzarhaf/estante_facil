<x-layout titulo="Acervo">
    <x-busca :acao="route('acervo.index')" :valor="$busca" placeholder="Título, autor, categoria ou editora" />

    <div class="cartao overflow-x-auto">
        @if ($obras->isEmpty())
            <x-vazio>Nenhuma obra encontrada.</x-vazio>
        @else
            <table class="tabela">
                <thead>
                    <tr><th>Título</th><th>Autores</th><th>Categorias</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($obras as $obra)
                        <tr>
                            <td>
                                <a href="{{ route('acervo.show', $obra) }}" class="font-medium text-emerald-800 hover:underline">{{ $obra->titulo }}</a>
                                @if ($obra->subtitulo)
                                    <span class="text-stone-500">— {{ $obra->subtitulo }}</span>
                                @endif
                            </td>
                            <td>{{ $obra->autores->pluck('nome')->join(', ') }}</td>
                            <td>{{ $obra->categorias->pluck('nome')->join(', ') ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $obras->links() }}</div>
        @endif
    </div>
</x-layout>
