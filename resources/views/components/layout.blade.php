@props(['titulo' => null])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}{{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3">
            <a href="{{ route('inicio') }}" class="text-lg font-semibold text-emerald-800">{{ config('app.name') }}</a>

            @auth
                <div class="flex flex-wrap gap-4 text-sm">
                    <a href="{{ route('acervo.index') }}" class="hover:text-emerald-700">Acervo</a>

                    @if (auth()->user()->ehLeitor())
                        <a href="{{ route('leitor.painel') }}" class="hover:text-emerald-700">Minha conta</a>
                    @else
                        <a href="{{ route('bibliotecario.painel') }}" class="hover:text-emerald-700">Atendimento</a>
                        <a href="{{ route('bibliotecario.leitores.index') }}" class="hover:text-emerald-700">Leitores</a>
                        <a href="{{ route('bibliotecario.obras.index') }}" class="hover:text-emerald-700">Obras</a>
                        <a href="{{ route('bibliotecario.edicoes.index') }}" class="hover:text-emerald-700">Edições</a>
                        <a href="{{ route('bibliotecario.exemplares.index') }}" class="hover:text-emerald-700">Exemplares</a>
                        <a href="{{ route('bibliotecario.autores.index') }}" class="hover:text-emerald-700">Autores</a>
                        <a href="{{ route('bibliotecario.categorias.index') }}" class="hover:text-emerald-700">Categorias</a>
                        <a href="{{ route('bibliotecario.editoras.index') }}" class="hover:text-emerald-700">Editoras</a>
                    @endif
                </div>

                <form method="POST" action="{{ route('logout') }}" class="ml-auto flex items-center gap-3 text-sm">
                    @csrf
                    <span class="text-stone-500">{{ auth()->user()->nome }}</span>
                    <button type="submit" class="botao-secundario">Sair</button>
                </form>
            @endauth
        </nav>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-6">
        @if ($titulo)
            <h1 class="text-2xl font-semibold">{{ $titulo }}</h1>
        @endif

        @session('sucesso')
            <div role="status" class="rounded-md border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ $value }}</div>
        @endsession

        @session('erro')
            <div role="alert" class="rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-900">{{ $value }}</div>
        @endsession

        {{ $slot }}
    </main>
</body>
</html>
