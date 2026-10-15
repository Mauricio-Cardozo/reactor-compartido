import './echo';

/**
 * Puente entre el canal `reactor` y el DOM.
 *
 * El servidor es la única autoridad: acá no se calcula nada, solo se dibuja
 * el snapshot que llega en cada `GameStateUpdated` (app/Events/GameStateUpdated.php).
 */
const state = {
    version: null,
};

function find(selector) {
    return document.querySelector(selector);
}

function setConnection(label, online) {
    const status = find('[data-connection-status]');
    const dot = find('[data-connection-dot]');

    if (status) {
        status.textContent = label;
    }

    if (dot) {
        dot.classList.toggle('bg-emerald-400', online);
        dot.classList.toggle('bg-slate-600', !online);
    }
}

function describe(action) {
    if (! action) {
        return 'actualización';
    }

    return action.summary ?? action.type ?? 'actualización';
}

function render(snapshot) {
    if (typeof snapshot.version === 'number') {
        // RNF-09: los eventos desordenados o repetidos no se aplican.
        if (state.version !== null && snapshot.version <= state.version) {
            return;
        }

        state.version = snapshot.version;
    }

    const lastEvent = find('[data-last-event]');

    if (! lastEvent) {
        return;
    }

    const time = snapshot.updated_at
        ? new Date(snapshot.updated_at).toLocaleTimeString()
        : new Date().toLocaleTimeString();

    lastEvent.textContent = `${describe(snapshot.action)} · ${time}`;
    lastEvent.dataset.version = snapshot.version ?? '';
}

function watchConnection() {
    const connection = window.Echo?.connector?.pusher?.connection;

    if (! connection) {
        setConnection('Sin conexión', false);

        return;
    }

    const labels = {
        connected: 'En vivo',
        connecting: 'Conectando…',
        disconnected: 'Reconectando…',
        unavailable: 'Sin conexión',
        failed: 'Sin conexión',
    };

    const paint = (pusherState) => setConnection(labels[pusherState] ?? pusherState, pusherState === 'connected');

    paint(connection.state);
    connection.bind('state_change', ({ current }) => paint(current));
}

window.Echo.channel('reactor').listen('.GameStateUpdated', (snapshot) => render(snapshot));

watchConnection();
