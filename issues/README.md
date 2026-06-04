# Issues & Activities — ln1_caja_rapida

Archivo: `issues/ws/activities.json` (ignorado por git vía `/issues` en `.gitignore`).

## Metadatos (raíz)

```json
{
    "project": "ln1_caja_rapida",
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
    "id": "WS-003",
    "title": "Seguridad / autenticación base",
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
    "dependencies": ["WS-002"]
}
```

## Estructura de una subtarea

```json
{
    "id": "WS-003-1",
    "title": "Título de la subtarea",
    "status": "pending",
    "layer": "infra | http | data | presentation",
    "validation": "comando shell para verificar",
    "guidance": {
        "agent": [
            "Ejecutar validation antes de marcar completed"
        ],
        "developer": {
            "goal": "Objetivo técnico",
            "files": ["app/Models/User.php"],
            "examples": ["Comportamiento esperado"],
            "references": [".cursor/rules/code-style.mdc"],
            "avoid": ["Anti-patrón explícito"]
        }
    }
}
```

### Capas (`layer` en subtareas)

| Valor | Ámbito |
|-------|--------|
| `infra` | Tooling, bootstrap, providers, Docker, rate limiting |
| `http` | Controllers, Requests, Resources, Middleware |
| `data` | Models, Repositories (SPs), DTOs — sin migraciones Laravel |
| `presentation` | Vue/Inertia (`resources/js/`) |

## Convención de IDs

- **WS-XXX**: Web Service (actividades fullstack)
- **WS-XXX-N**: subtareas (ej. `WS-003-1`)

## Cómo trabajar con las actividades

1. Consultar `issues/ws/activities.json` (metadatos + `activities[]`).
2. Tomar la siguiente actividad `pending` cuyas `dependencies` estén `completed`.
3. Leer `guidance` de la actividad y de cada subtarea antes de codificar.
4. Cambiar la actividad a `in_progress`.
5. Completar subtareas en orden: aplicar `guidance.agent` y `guidance.developer`, ejecutar `validation`, marcar `completed`.
6. Verificar `acceptanceCriteria` de la actividad; marcar actividad `completed` y commit.

## Jira

Sincronizar una actividad con el proyecto LN1SCRUM usando el skill global `activities-to-jira` y el ID (ej. `activities-to-jira WS-003`). Las descripciones en Jira usan plantillas breves (padre) y guidance developer (subtareas).

## Reglas del agente

Detalle operativo: `.cursor/rules/workflow.mdc`.
