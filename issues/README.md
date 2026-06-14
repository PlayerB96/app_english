# Issues & Activities — app_english

Archivo: `issues/ws/activities.json` (ignorado por git vía `/issues` en `.gitignore`).

## Metadatos (raíz)

```json
{
    "project": "app_english",
    "layer": "web-service",
    "architecture": "layered-repository-service-inertia",
    "packageManager": "composer + pnpm",
    "activities": []
}
```

## Protocolo de estados

| Estado | Significado |
|--------|-------------|
| `pending` | Actividad o subtarea pendiente, no iniciada |
| `in_progress` | En desarrollo activo |
| `completed` | Terminada y verificada (`validation` ejecutada en subtareas) |
| `blocked` | Bloqueada por dependencias externas o decisiones pendientes |

## Estructura de una actividad

```json
{
    "id": "WS-010",
    "title": "Esquema PostgreSQL y modelos base",
    "category": "arquitectura | patron-diseno | seguridad | feature | bugfix",
    "architecture": "layered-repository-service-inertia",
    "status": "pending",
    "priority": "high | medium | low",
    "description": "Descripción de la actividad",
    "acceptanceCriteria": ["Criterio 1", "Criterio 2"],
    "guidance": {
        "agent": ["Instrucción para el agente"],
        "developer": {
            "goal": "Objetivo de la actividad",
            "context": ["Nota opcional"],
            "references": [".cursor/rules/project.mdc"]
        }
    },
    "subtasks": [],
    "dependencies": ["WS-001"]
}
```

## Estructura de una subtarea

```json
{
    "id": "WS-010-1",
    "title": "Migraciones learning_tracks y practice_sessions",
    "status": "pending",
    "layer": "infra | http | data | presentation",
    "validation": "php artisan migrate --pretend",
    "guidance": {
        "agent": [
            "Ejecutar validation antes de marcar completed"
        ],
        "developer": {
            "goal": "Objetivo técnico",
            "files": ["database/migrations/"],
            "examples": ["migrate exitoso"],
            "references": [".cursor/rules/code-style.mdc"],
            "avoid": ["DDL fuera de migraciones"]
        }
    }
}
```

### Capas (`layer` en subtareas)

| Valor | Ámbito |
|-------|--------|
| `infra` | Tooling, bootstrap, providers, Docker, PostgreSQL, rate limiting |
| `http` | Controllers, Requests, Resources, Middleware |
| `data` | Models, Repositories, DTOs, **migraciones** |
| `presentation` | Vue/Inertia, composables de voz (`resources/js/`) |

## Convención de IDs

- **WS-XXX**: Web Service (actividades fullstack)
- **WS-XXX-N**: subtareas (ej. `WS-010-1`)

## Cómo trabajar con las actividades

1. Consultar `issues/ws/activities.json` (metadatos + `activities[]`).
2. Tomar la siguiente actividad `pending` cuyas `dependencies` estén `completed`.
3. Leer `guidance` de la actividad y de cada subtarea antes de codificar.
4. Cambiar la actividad a `in_progress`.
5. Completar subtareas en orden: aplicar `guidance.agent` y `guidance.developer`, ejecutar `validation`, marcar `completed`.
6. Verificar `acceptanceCriteria` de la actividad; marcar actividad `completed` y commit.

## Reglas del agente

Detalle operativo: `.cursor/rules/workflow.mdc`.
