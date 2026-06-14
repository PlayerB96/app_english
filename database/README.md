# Base de datos

Esquema de aplicación en **PostgreSQL**, gestionado con **migraciones Laravel**.

## Convenciones

- Tablas en snake_case plural (`users`, `practice_sessions`, `questions`, `learning_tracks`).
- FK explícitas con `constrained()` y `cascadeOnDelete()` donde aplique.
- Índices en columnas de filtro frecuente (`user_id`, `track_id`, `created_at`).
- Enums de dominio preferiblemente como columnas `string` con cast a PHP Enum en el Model.

## Tablas previstas (roadmap)

| Tabla | Propósito |
|-------|-----------|
| `users` | Developers autenticados |
| `learning_tracks` | Rutas de aprendizaje temáticas |
| `practice_sessions` | Sesiones de práctica por usuario |
| `questions` | Preguntas (generadas IA o seed) |
| `answers` | Respuestas del usuario (texto/voz transcrita) |
| `progress_snapshots` | Puntos de la curva de aprendizaje |

## Seeders

- `LearningTrackSeeder`: tracks iniciales (dev vocabulary, interviews, documentation).
- `DatabaseSeeder`: orquesta seeders en entorno local/demo.

## Tests

Los factories en `database/factories/` alimentan tests Feature/Unit sin depender de datos reales de producción.

## Comandos

```bash
php artisan migrate
php artisan migrate:fresh --seed   # solo local
```
