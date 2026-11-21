@php($autoresSelecionados = collect(old('autores', $obra->autores?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id))
@php($categoriasSelecionadas = collect(old('categorias', $obra->categorias?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id))

<x-layout :titulo="$obra->exists ? 'Editar obra' : 'Cadastrar obra'">
    <form method="POST" action="{{ $obra->exists ? route('bibliotecario.obras.update', $obra) : route('bibliotecario.obras.store') }}" class="cartao space-y-4">
        @csrf
        @if ($obra->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <x-campo nome="titulo" rotulo="Título" :valor="$obra->titulo" required class="sm:col-span-2" />
            <x-campo nome="subtitulo" rotulo="Subtítulo" :valor="$obra->subtitulo" class="sm:col-span-2" />
            <x-campo nome="idioma_original" rotulo="Idioma original" :valor="$obra->idioma_original" />
            <x-campo nome="url_acesso" rotulo="URL de acesso (obras online)" tipo="url" :valor="$obra->url_acesso" />

            <div>
                <label for="autores" class="rotulo">Autores (ao menos um)</label>
                <select id="autores" name="autores[]" multiple required size="8" class="entrada">
                    @foreach ($autores as $autor)
                        <option value="{{ $autor->id }}" @selected($autoresSelecionados->contains($autor->id))>{{ $autor->nome }}</option>
                    @endforeach
                </select>
                <x-erro-de-campo nome="autores" />
                <a href="{{ route('bibliotecario.autores.create') }}" class="mt-1 inline-block text-xs text-emerald-800 hover:underline">+ cadastrar autor</a>
            </div>

            <div>
                <label for="categorias" class="rotulo">Categorias</label>
                <select id="categorias" name="categorias[]" multiple size="8" class="entrada">
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected($categoriasSelecionadas->contains($categoria->id))>{{ $categoria->nome }}</option>
                    @endforeach
                </select>
                <x-erro-de-campo nome="categorias" />
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="botao-primario">Salvar</button>
            <a href="{{ route('bibliotecario.obras.index') }}" class="botao-secundario">Cancelar</a>
        </div>
    </form>
</x-layout>
