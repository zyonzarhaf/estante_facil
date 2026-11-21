@props(['acao', 'valor' => '', 'placeholder' => 'Buscar…'])

<form method="GET" action="{{ $acao }}" class="flex gap-2">
    <input type="search" name="busca" value="{{ $valor }}" placeholder="{{ $placeholder }}" aria-label="Buscar" class="entrada max-w-md">
    <button type="submit" class="botao-secundario">Buscar</button>
</form>
