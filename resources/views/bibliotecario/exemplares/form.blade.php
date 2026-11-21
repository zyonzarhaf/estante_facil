<x-layout :titulo="$exemplar->exists ? 'Editar exemplar' : 'Cadastrar exemplar'">
    <form method="POST" action="{{ $exemplar->exists ? route('bibliotecario.exemplares.update', $exemplar) : route('bibliotecario.exemplares.store') }}" class="cartao grid gap-4 sm:grid-cols-2">
        @csrf
        <x-retorno />
        @if ($exemplar->exists)
            @method('PUT')
        @endif

        <div class="sm:col-span-2">
            <label for="edicao_id" class="rotulo">Edição</label>
            <select id="edicao_id" name="edicao_id" required class="entrada">
                <option value="">Selecione…</option>
                @foreach ($edicoes as $edicao)
                    <option value="{{ $edicao->id }}" @selected((int) old('edicao_id', $exemplar->edicao_id) === $edicao->id)>
                        {{ $edicao->obra->titulo }} — {{ $edicao->numero_edicao }}ª ed., {{ $edicao->editora->nome }} (ISBN {{ $edicao->isbn }})
                    </option>
                @endforeach
            </select>
            <x-erro-de-campo nome="edicao_id" />
            <x-link-cadastro rota="bibliotecario.edicoes.create" rotulo="cadastrar edição" />
        </div>

        <x-campo nome="codigo_tombo" rotulo="Código de tombo" :valor="$exemplar->codigo_tombo" required />
        <x-campo nome="data_aquisicao" rotulo="Data de aquisição" tipo="date" :valor="$exemplar->data_aquisicao?->format('Y-m-d')" required />

        <div>
            <label for="estado_conservacao" class="rotulo">Estado de conservação</label>
            <select id="estado_conservacao" name="estado_conservacao" required class="entrada">
                @foreach ($estados as $estado)
                    <option value="{{ $estado->value }}" @selected(old('estado_conservacao', $exemplar->estado_conservacao?->value) === $estado->value)>{{ $estado->rotulo() }}</option>
                @endforeach
            </select>
            <x-erro-de-campo nome="estado_conservacao" />
        </div>

        <div class="flex gap-3 sm:col-span-2">
            <button type="submit" class="botao-primario">Salvar</button>
            <a href="{{ request('retorno') ?: route('bibliotecario.exemplares.index') }}" class="botao-secundario">Cancelar</a>
        </div>
    </form>
</x-layout>
