@php($usuario = $leitor?->usuario)
@php($endereco = $usuario?->endereco)

<x-layout :titulo="$leitor ? 'Editar leitor' : 'Cadastrar leitor'">
    <form method="POST" action="{{ $leitor ? route('bibliotecario.leitores.update', $leitor) : route('bibliotecario.leitores.store') }}" class="cartao space-y-6">
        @csrf
        @if ($leitor)
            @method('PUT')
        @endif

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-2 font-semibold">Dados pessoais</legend>
            <x-campo nome="nome" rotulo="Nome" :valor="$usuario?->nome" required class="sm:col-span-2" />
            <x-campo nome="cpf" rotulo="CPF" :valor="$usuario?->cpf" required inputmode="numeric" />
            <x-campo nome="telefone" rotulo="Telefone" :valor="$usuario?->telefone" />
            <x-campo nome="email" rotulo="E-mail" tipo="email" :valor="$usuario?->email" required class="sm:col-span-2" />
            <x-campo nome="senha" rotulo="{{ $leitor ? 'Nova senha (opcional)' : 'Senha' }}" tipo="password" :required="! $leitor" autocomplete="new-password" />
            <x-campo nome="senha_confirmation" rotulo="Confirmação da senha" tipo="password" autocomplete="new-password" />
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-6">
            <legend class="mb-2 font-semibold">Endereço</legend>
            <x-campo nome="cep" rotulo="CEP" :valor="$endereco?->cep" required inputmode="numeric" class="sm:col-span-2" />
            <x-campo nome="logradouro" rotulo="Logradouro" :valor="$endereco?->logradouro" required class="sm:col-span-4" />
            <x-campo nome="numero" rotulo="Número" :valor="$endereco?->numero" required class="sm:col-span-1" />
            <x-campo nome="complemento" rotulo="Complemento" :valor="$endereco?->complemento" class="sm:col-span-2" />
            <x-campo nome="bairro" rotulo="Bairro" :valor="$endereco?->bairro" required class="sm:col-span-3" />
            <x-campo nome="cidade" rotulo="Cidade" :valor="$endereco?->cidade" required class="sm:col-span-4" />
            <x-campo nome="uf" rotulo="UF" :valor="$endereco?->uf" required maxlength="2" class="sm:col-span-2" />
        </fieldset>

        <div class="flex gap-3">
            <button type="submit" class="botao-primario">Salvar</button>
            <a href="{{ $leitor ? route('bibliotecario.leitores.show', $leitor) : route('bibliotecario.leitores.index') }}" class="botao-secundario">Cancelar</a>
        </div>
    </form>
</x-layout>
