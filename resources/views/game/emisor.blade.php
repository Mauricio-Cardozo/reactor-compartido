@extends('game.layout')

@section('title', 'Emisor')
@section('screen', 'emisor')

@section('content')
    <div class="space-y-6">
        <header class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight">Operador · Emisor</h1>
            <p class="text-sm text-slate-400">Producí energía y activá la sobrecarga cuando convenga.</p>
        </header>

        <section class="space-y-4 rounded-xl border border-slate-800 bg-slate-900/40 p-5">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Reactor</p>
                <p class="mt-2 text-sm text-slate-400">
                    Clickear para producir energía. El servidor agrupa por lote: cada ~200 ms se envía lo acumulado.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-click-button
                        class="rounded-md bg-emerald-500 px-6 py-3 text-base font-semibold text-slate-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 active:scale-[0.98]">
                    Activar reactor
                </button>
                <p class="text-xs text-slate-500">
                    Lote pendiente: <span data-batch-count>0</span>
                </p>
            </div>
        </section>
    </div>
@endsection
