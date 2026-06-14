# Patrón Repository → Service (tests)

Clases de ejemplo del patrón layered con Eloquent para **User** (dominio de prueba del patrón WS-002).

| Clase | Ubicación |
|-------|-----------|
| `UserRepository` | `app/Repositories/UserRepository.php` |
| `UserService` | `app/Services/UserService.php` |
| `StoreUserRequest` | `app/Http/Requests/StoreUserRequest.php` |

Tests: `RepositoryTest`, `RequestTest`.

**Auth de producción** usa `AuthRepository` + `AuthService` (no estas clases de plantilla).

Acceso a PostgreSQL: Eloquent + **functions** (`fn_*`) + **views** (`vw_*`) en Repositories. Ver `database/pg-catalog.md`.
