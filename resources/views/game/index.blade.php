@extends('game.layout')

@section('title', 'Elegir rol')

@section('content')
    <div class="space-y-6">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold tracking-tight">Reactor Compartido</h1>
            <p class="max-w-prose text-sm text-slate-400">
                Dos roles sobre un mismo reactor: uno produce energía, el otro la convierte en mejoras.
                Abrí una pantalla en cada pestaña para jugar en simultáneo.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('game.emisor') }}"
               class="group rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition hover:border-emerald-500/60 hover:bg-slate-900">
                <h2 class="font-semibold">Operador · Emisor</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Clickea el reactor para producir energía y activa la sobrecarga.
                </p>
                <span class="mt-4 inline-block text-sm text-emerald-400 group-hover:underline">Entrar →</span>
            </a>

            <a href="{{ route('game.receptor') }}"
               class="group rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition hover:border-sky-500/60 hover:bg-slate-900">
                <h2 class="font-semibold">Ingeniero · Receptor</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Mirá la producción en vivo y comprá mejoras con los créditos generados.
                </p>
                <span class="mt-4 inline-block text-sm text-sky-400 group-hover:underline">Entrar →</span>
            </a>
        </div>
    </div>
@endsection
