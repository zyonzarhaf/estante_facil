@props(['nome', 'rotulo', 'tipo' => 'text', 'valor' => null])

<div {{ $attributes->only('class') }}>
    <label for="{{ $nome }}" class="rotulo">{{ $rotulo }}</label>
    <input id="{{ $nome }}" name="{{ $nome }}" type="{{ $tipo }}"
           value="{{ $tipo === 'password' ? '' : old($nome, $valor) }}"
           {{ $attributes->except('class')->merge(['class' => 'entrada']) }}>
    @error($nome)
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
