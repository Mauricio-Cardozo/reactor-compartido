@extends('game.layout')

@section('title', 'Emisor')
@section('screen', 'emisor')

@section('content')
    <div class="space-y-6">
        <header class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight">Operador · Emisor</h1>
            <p class="text-sm text-slate-400">Producí energía y activá la sobrecarga cuando convenga.</p>
        </header>

        <section class="rounded-xl border border-slate-800 bg-slate-900/40 p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Estado del reactor</p>
            <p class="mt-2 text-sm text-slate-400">
                Todavía no hay estado que mostrar: los recursos y el control de clicks llegan en el Hito 2.
            </p>
        </section>
    </div>
@endsection
