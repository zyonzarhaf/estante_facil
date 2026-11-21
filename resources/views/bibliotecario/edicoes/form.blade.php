<x-layout :titulo="$edicao->exists ? 'Editar edição' : 'Cadastrar edição'">
    <form method="POST" action="{{ $edicao->exists ? route('bibliotecario.edicoes.update', $edicao) : route('bibliotecario.edicoes.store') }}" class="cartao grid gap-4 sm:grid-cols-2">
        @csrf
        <x-retorno />
        @if ($edicao->exists)
            @method('PUT')
        @endif

        <div class="sm:col-span-2">
            <label for="obra_id" class="rotulo">Obra</label>
            <select id="obra_id" name="obra_id" required class="entrada">
                <option value="">Selecione…</option>
                @foreach ($obras as $obra)
                    <option value="{{ $obra->id }}" @selected((int) old('obra_id', $edicao->obra_id) === $obra->id)>{{ $obra->titulo }}</option>
                @endforeach
            </select>
            <x-erro-de-campo nome="obra_id" />
            <x-link-cadastro rota="bibliotecario.obras.create" rotulo="cadastrar obra" />
        </div>

        <div>
            <label for="editora_id" class="rotulo">Editora</label>
            <select id="editora_id" name="editora_id" required class="entrada">
                <option value="">Selecione…</option>
                @foreach ($editoras as $editora)
                    <option value="{{ $editora->id }}" @selected((int) old('editora_id', $edicao->editora_id) === $editora->id)>{{ $editora->nome }}</option>
                @endforeach
            </select>
            <x-erro-de-campo nome="editora_id" />
            <x-link-cadastro rota="bibliotecario.editoras.create" rotulo="cadastrar editora" />
        </div>

        <x-campo nome="isbn" rotulo="ISBN (13 dígitos)" :valor="$edicao->isbn" required inputmode="numeric" />
        <x-campo nome="numero_edicao" rotulo="Número da edição" tipo="number" min="1" :valor="$edicao->numero_edicao" required />
        <x-campo nome="ano_publicacao" rotulo="Ano de publicação" tipo="number" min="1" :valor="$edicao->ano_publicacao" />
        <x-campo nome="numero_paginas" rotulo="Número de páginas" tipo="number" min="1" :valor="$edicao->numero_paginas" />
        <x-campo nome="idioma" rotulo="Idioma" :valor="$edicao->idioma" />

        <div class="flex gap-3 sm:col-span-2">
            <button type="submit" class="botao-primario">Salvar</button>
            <a href="{{ request('retorno') ?: route('bibliotecario.edicoes.index') }}" class="botao-secundario">Cancelar</a>
        </div>
    </form>
</x-layout>
