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
                <p class="text-xs uppercase tracking-wide text-slate-500">Estado del reactor</p>
                <p class="mt-2 text-sm text-slate-400">
                    Todavía no hay recursos que mostrar: los clicks y la energía llegan en el Hito 2.
                </p>
            </div>

            <div class="border-t border-slate-800 pt-4">
                <button type="button"
                        data-test-broadcast
                        data-url="{{ route('game.ping') }}"
                        class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-slate-950 transition hover:bg-emerald-400 disabled:opacity-50">
                    Emitir evento de prueba
                </button>
                <p class="mt-2 text-xs text-slate-500">
                    Temporal (Hito 1): emite un <code>GameStateUpdated</code> para comprobar que la otra pestaña se actualiza.
                </p>
            </div>
        </section>
    </div>
@endsection
