# Catálogo PostgreSQL — functions y views

Fuente de verdad de contratos SQL antes de implementar lógica en Repositories.
**No** usar stored procedures estilo SQL Server (`EXEC`, `dbo.usp_*`).

Estados:

| Estado | Significado |
|--------|-------------|
| `planned` | Diseñado; SQL pendiente de implementar |
| `ready` | SQL en `database/sql/` y migración aplicada |
| `blocked` | Pendiente de definir contrato (parámetros / columnas) |

## Functions (`fn_*`)

| Function | Propósito | Parámetros | Retorno | Estado |
|----------|-----------|------------|---------|--------|
| `fn_calculate_learning_level` | Nivel estimado del developer según historial | `p_user_id bigint` | `TABLE (level text, score numeric, streak int)` | `planned` |
| `fn_session_accuracy` | Precisión de respuestas en una sesión | `p_session_id bigint` | `TABLE (total int, correct int, accuracy_pct numeric)` | `planned` |

## Views (`vw_*`)

| View | Propósito | Columnas principales | Estado |
|------|-----------|---------------------|--------|
| `vw_developer_progress_summary` | Resumen para dashboard y curva de aprendizaje | `user_id`, `total_sessions`, `avg_accuracy`, `current_level`, `last_practice_at` | `planned` |
| `vw_track_leaderboard` | Ranking por learning track | `track_id`, `user_id`, `score`, `rank` | `planned` |

## Notas de integración

### Functions

- Llamar desde Repositories: `DB::select('SELECT * FROM fn_nombre(?)', [$param])`
- Mapear filas a DTOs en el Repository; el Service no parsea `stdClass`
- SQL versionado en `database/sql/functions/fn_nombre.sql`
- Desplegar con migración Laravel (`DB::unprepared()`)

### Views

- Solo lectura desde la app
- Llamar desde Repositories: `DB::select('SELECT * FROM vw_nombre WHERE ...')`
- SQL versionado en `database/sql/views/vw_nombre.sql`

### Convención de nombres

- Functions: `fn_<verbo>_<sustantivo>` (ej: `fn_calculate_learning_level`)
- Views: `vw_<dominio>_<descripcion>` (ej: `vw_developer_progress_summary`)
