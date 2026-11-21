@props(['editar', 'excluir'])

<div class="flex justify-end gap-2">
    <a href="{{ $editar }}" class="botao-secundario">Editar</a>
    <form method="POST" action="{{ $excluir }}" onsubmit="return confirm('Excluir este registro?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="botao-perigo">Excluir</button>
    </form>
</div>
