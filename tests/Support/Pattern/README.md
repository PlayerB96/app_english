# Patrón WS-002 (solo tests)

Las clases siguientes demuestran el patrón Repository → Service → Form Request con Eloquent.
**No deben usarse en rutas ni controllers de producción** (auth real: `MobileUser` + SP legacy).

| Clase | Ubicación |
|-------|-----------|
| `User` | `app/Models/User.php` |
| `UserRepository` | `app/Repositories/UserRepository.php` |
| `UserService` | `app/Services/UserService.php` |
| `StoreUserRequest` | `app/Http/Requests/StoreUserRequest.php` |

Tests que las ejercitan: `RepositoryTest`, `RequestTest`.
