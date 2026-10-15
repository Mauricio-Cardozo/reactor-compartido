# Changelog

Todas las fechas en formato ISO 8601. El proyecto sigue [Versionado Semántico](https://semver.org/lang/es/)
y los hitos se etiquetan al cerrarse (`v0.1.0` = Hito 1, etc.).

## [0.1.0] - 2026-10-15

### Agregado

- Pantallas de operador (`/emisor`) e ingeniero (`/receptor`) con layout compartido y selector de rol en `/` (#8).
- Evento `GameStateUpdated` sobre el canal público `reactor`, con payload filtrado y dispatch posterior al commit (#9).
- Suscripción a Echo en ambas pantallas, indicador de conexión y descarte de eventos desordenados por `version` (#10).
- Endpoint temporal `POST /game/ping` (solo `local`/`testing`) para verificar el broadcast; lo reemplaza el endpoint de clicks.

### Cambiado

- `APP_NAME` pasa a "Reactor Compartido".
- La entrada de Vite deja de ser `resources/js/app.js` y pasa a `resources/js/game.js`.
