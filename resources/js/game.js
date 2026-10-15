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

const selectors = {
    status: '[data-connection-status]',
    dot: '[data-connection-dot]',
    lastEvent: '[data-last-event]',
    energy: '[data-energy]',
    credits: '[data-credits]',
    clickButton: '[data-click-button]',
    batchCount: '[data-batch-count]',
};

let pendingClicks = 0;
let flushTimer = null;
const FLUSH_DELAY_MS = 200;

function find(selector) {
    return document.querySelector(selector);
}

function setConnection(label, online) {
    const status = find(selectors.status);
    const dot = find(selectors.dot);

    if (status) {
        status.textContent = label;
    }

    if (dot) {
        dot.classList.toggle('bg-emerald-400', online);
        dot.classList.toggle('bg-slate-600', ! online);
    }
}

function describe(action) {
    if (! action) {
        return 'actualización';
    }

    if (action.type === 'click' && typeof action.clicks === 'number') {
        return `click ×${action.clicks}`;
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

    const lastEvent = find(selectors.lastEvent);
    if (lastEvent) {
        const time = snapshot.updated_at
            ? new Date(snapshot.updated_at).toLocaleTimeString()
            : new Date().toLocaleTimeString();
        lastEvent.textContent = `${describe(snapshot.action)} · ${time}`;
        lastEvent.dataset.version = snapshot.version ?? '';
    }

    const energyEl = find(selectors.energy);
    const creditsEl = find(selectors.credits);
    if (energyEl && typeof snapshot.energy === 'string') {
        energyEl.textContent = snapshot.energy;
    }
    if (creditsEl && typeof snapshot.credits === 'string') {
        creditsEl.textContent = snapshot.credits;
    }
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

function updateBatchCount() {
    const el = find(selectors.batchCount);
    if (el) {
        el.textContent = pendingClicks.toString();
    }
}

async function postJson(url, data) {
    const token = find('meta[name="csrf-token"]')?.content ?? '';

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify(data),
    });

    return response;
}

async function fetchState() {
    const stateUrl = window.location.origin + '/game/state';
    try {
        const response = await fetch(stateUrl, {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        if (! response.ok) {
            return;
        }

        const snapshot = await response.json();
        render(snapshot);
    } catch (e) {
        // Ignorar errores de estado inicial: se actualiza vía broadcast.
    }
}

function flushClicks() {
    if (flushTimer) {
        clearTimeout(flushTimer);
        flushTimer = null;
    }

    if (pendingClicks <= 0) {
        updateBatchCount();

        return;
    }

    const clicks = pendingClicks;
    pendingClicks = 0;
    updateBatchCount();

    const url = window.location.origin + '/game/click';
    postJson(url, { clicks }).catch(() => {
        // Si falla, descartamos el lote para evitar duplicados por reintento
        // no coordinado. El throttle devolverá 429 con Retry-After.
    });
}

function queueClick() {
    pendingClicks++;
    updateBatchCount();

    if (flushTimer) {
        clearTimeout(flushTimer);
    }

    flushTimer = setTimeout(flushClicks, FLUSH_DELAY_MS);
}

function wireClickButton() {
    const button = find(selectors.clickButton);
    if (! button) {
        return;
    }

    button.addEventListener('pointerdown', (event) => {
        event.preventDefault();
        queueClick();
    });
}

function init() {
    window.Echo.channel('reactor').listen('.GameStateUpdated', (snapshot) => render(snapshot));

    watchConnection();
    wireClickButton();
    fetchState();
}

init();
