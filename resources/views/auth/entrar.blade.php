<x-layout>
    <div class="cartao mx-auto mt-10 max-w-sm">
        <h1 class="mb-4 text-xl font-semibold">Entrar</h1>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf
            <x-campo nome="email" rotulo="E-mail" tipo="email" required autofocus autocomplete="username" />
            <x-campo nome="senha" rotulo="Senha" tipo="password" required autocomplete="current-password" />

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="lembrar" value="1" class="rounded border-stone-300">
                Lembrar de mim
            </label>

            <button type="submit" class="botao-primario w-full">Entrar</button>
        </form>
    </div>
</x-layout>
