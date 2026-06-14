# Base de datos — PostgreSQL

Esquema de aplicación en **PostgreSQL exclusivamente**. Sin SQL Server ni stored procedures.

## Capas de persistencia

| Capa | Herramienta | Ubicación |
|------|-------------|-----------|
| Tablas, índices, FK | Migraciones Laravel | `database/migrations/` |
| Functions (`fn_*`) | SQL versionado + migración | `database/sql/functions/` |
| Views (`vw_*`) | SQL versionado + migración | `database/sql/views/` |
| Datos demo | Seeders | `database/seeders/` |
| Catálogo | Documentación | `database/pg-catalog.md` |

## Convenciones

- Tablas: snake_case plural (`users`, `practice_sessions`, `questions`)
- Functions: prefijo `fn_`, retorno tipado (`RETURNS TABLE`, `RETURNS jsonb`, etc.)
- Views: prefijo `vw_`, solo lectura desde la app
- FK con `constrained()` y `cascadeOnDelete()` donde aplique
- Enums de dominio: columna `string` + cast a PHP Enum en el Model

## Tablas previstas

| Tabla | Propósito |
|-------|-----------|
| `users` | Developers autenticados |
| `learning_tracks` | Rutas de aprendizaje temáticas |
| `practice_sessions` | Sesiones de práctica por usuario |
| `questions` | Preguntas (generadas IA o seed) |
| `answers` | Respuestas del usuario (texto/voz transcrita) |
| `progress_snapshots` | Puntos de la curva de aprendizaje |

## Functions y views previstas (ejemplos)

| Artefacto | Propósito |
|-----------|-----------|
| `fn_calculate_learning_level(user_id)` | Nivel estimado del developer |
| `fn_session_accuracy(session_id)` | Precisión de una sesión |
| `vw_developer_progress_summary` | Resumen para dashboard / curva |
| `vw_track_leaderboard` | Ranking por track (futuro) |

Documentar contratos completos en [`pg-catalog.md`](pg-catalog.md) antes de implementar.

## Desplegar una function desde Laravel

```php
// database/migrations/2026_xx_xx_create_fn_calculate_learning_level.php
public function up(): void
{
    DB::unprepared(file_get_contents(database_path('sql/functions/fn_calculate_learning_level.sql')));
}

public function down(): void
{
    DB::unprepared('DROP FUNCTION IF EXISTS fn_calculate_learning_level(bigint);');
}
```

## Llamada desde Repository

```php
$rows = DB::select('SELECT * FROM fn_calculate_learning_level(?)', [$userId]);
$summary = DB::selectOne('SELECT * FROM vw_developer_progress_summary WHERE user_id = ?', [$userId]);
```

## Comandos

```bash
php artisan migrate
php artisan migrate:fresh --seed   # solo local
```

## Seeders

- `UserSeeder`: usuarios demo (`learner@app-english.test`, `admin@app-english.test`)
- `LearningTrackSeeder`: tracks iniciales (pendiente WS-012+)
- `DatabaseSeeder`: orquesta seeders en entorno local/demo
