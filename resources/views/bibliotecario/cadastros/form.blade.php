<x-layout :titulo="($registro ? 'Editar ' : 'Cadastrar ').$rotulos['singular']">
    <form method="POST" action="{{ $registro ? route('bibliotecario.'.$recurso.'.update', $registro) : route('bibliotecario.'.$recurso.'.store') }}" class="cartao max-w-lg space-y-4">
        @csrf
        <x-retorno />
        @if ($registro)
            @method('PUT')
        @endif
        <x-campo nome="nome" rotulo="Nome" :valor="$registro?->nome" required autofocus />
        <div class="flex gap-3">
            <button type="submit" class="botao-primario">Salvar</button>
            <a href="{{ request('retorno') ?: route('bibliotecario.'.$recurso.'.index') }}" class="botao-secundario">Cancelar</a>
        </div>
    </form>
</x-layout>
