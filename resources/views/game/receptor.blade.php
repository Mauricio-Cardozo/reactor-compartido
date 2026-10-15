@extends('game.layout')

@section('title', 'Receptor')
@section('screen', 'receptor')

@section('content')
    <div class="space-y-6">
        <header class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight">Ingeniero · Receptor</h1>
            <p class="text-sm text-slate-400">Seguí la producción en vivo y comprá mejoras.</p>
        </header>

        <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Producción en vivo</p>
            <p class="mt-2 text-sm text-slate-400">
                Acá van a aparecer energía, créditos y mejoras: llegan en el Hito 3.
            </p>
        </section>
    </div>
@endsection
