<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Reactor') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased">
    <div class="mx-auto flex min-h-full max-w-5xl flex-col gap-6 p-4 sm:p-6">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <span class="inline-block size-3 rounded-full bg-emerald-400 shadow-[0_0_12px] shadow-emerald-400/70"></span>
                <a href="{{ route('game.index') }}" class="text-lg font-semibold tracking-tight">
                    {{ config('app.name') }}
                </a>
            </div>

            <nav class="flex items-center gap-1 text-sm">
                @foreach (['game.emisor' => 'Emisor', 'game.receptor' => 'Receptor'] as $route => $label)
                    <a href="{{ route($route) }}"
                       @class([
                           'rounded-md px-3 py-1.5 transition',
                           'bg-slate-800 text-white' => request()->routeIs($route),
                           'text-slate-400 hover:bg-slate-900 hover:text-slate-200' => ! request()->routeIs($route),
                       ])
                       @if (request()->routeIs($route)) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <p class="flex items-center gap-2 text-xs text-slate-400">
                <span class="inline-block size-2 rounded-full bg-slate-600" data-connection-dot></span>
                <span data-connection-status>Sin conexión</span>
            </p>
        </header>

        <main class="flex-1" @hasSection('screen') data-screen="@yield('screen')" @endif>
            @yield('content')
        </main>

        <footer class="border-t border-slate-800 pt-4 text-xs text-slate-500">
            El servidor es la única autoridad: el navegador solo pide acciones y dibuja el estado recibido.
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
