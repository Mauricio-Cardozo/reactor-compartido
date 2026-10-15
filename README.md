# Reactor Compartido

Idle/clicker cooperativo en tiempo real para dos jugadores con roles asimétricos sobre un **reactor compartido**.

| Rol | Pantalla | Qué hace |
|---|---|---|
| **Operador** (emisor) | `/emisor` | Clickea el reactor para producir energía y activa la **sobrecarga** (acción con enfriamiento). |
| **Ingeniero** (receptor) | `/receptor` | Ve la producción en vivo, compra mejoras con créditos y gestiona generadores automáticos. |

**Regla de oro:** el servidor es la única autoridad. El navegador solo pide acciones y dibuja el estado que le llega. Nunca calcula recursos por su cuenta.

## Stack

- Laravel 12 · Reverb · Laravel Echo · Blade + JS
- MySQL/SQLite · Logs JSON

## Instalación

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # o usar MySQL configurando DB_* en .env
php artisan migrate --seed
npm run build                    # o npm run dev en desarrollo
```

Levantar los tres procesos:

```bash
php artisan serve        # app en http://localhost:8000
php artisan reverb:start # websocket en el puerto 8080
npm run dev              # assets en desarrollo
```

## Tests y calidad

```bash
./vendor/bin/pint --test   # formateo (Laravel Pint)
php artisan test           # tests
```

La CI de GitHub Actions corre ambos en cada PR y bloquea el merge en `main` si fallan.

## Arquitectura

- El navegador envía `POST /game/click|upgrade|overload`; el servidor abre una transacción, bloquea la fila del estado (`lockForUpdate`), aplica el tick idle, valida y modifica.
- Solo después del `commit` se emite el evento `GameStateUpdated` por el canal público `reactor` vía Reverb (nunca dentro de la transacción).
- Los clientes ignoran eventos con `version` menor o igual a la ya mostrada.
- Balance y reglas viven en `config/game.php`; la lógica de juego vive en `app/Services/GameService.php`.
- Recursos calculados con BCMath/enteros escalados (escala 4); ningún valor lo decide el cliente.

## Trazabilidad (logs JSON)

Canales en `storage/logs/`: `daily_json` (general), `game` (acciones), `broadcast` (eventos emitidos), `sql` (consultas lentas >50 ms).

```bash
# Últimas acciones rechazadas
jq -c 'select(.event == "action.rejected")' storage/logs/game-$(date +%Y-%m-%d).log

# Broadcasts emitidos
jq -c 'select(.event == "broadcast.sent")' storage/logs/broadcast-$(date +%Y-%m-%d).log

# Consultas lentas
jq -c 'select(.event == "sql.slow")' storage/logs/sql-$(date +%Y-%m-%d).log
```

## Git

- GitHub Flow: `main` siempre estable; una rama por issue (`feat/12-click-endpoint`).
- Commits atómicos con [Conventional Commits](https://www.conventionalcommits.org/).
- PRs chicos (ideal <300 líneas), con `Closes #n` y CI verde antes del merge.
- Cada hito se etiqueta al cerrarse (`v0.1.0` tras el Hito 1) y su resumen queda anotado en [CHANGELOG.md](CHANGELOG.md).
