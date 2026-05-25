# Issues & Activities — ln1_caja_rapida

## Protocolo de estados

| Estado | Significado |
|--------|-------------|
| `pending` | Actividad pendiente, no iniciada |
| `in_progress` | Actividad en desarrollo activo |
| `completed` | Actividad terminada y verificada |
| `blocked` | Actividad bloqueada por dependencias externas o decisiones pendientes |

## Estructura de una actividad

```json
{
    "id": "WS-001",
    "title": "Título de la actividad",
    "category": "arquitectura | patron-diseno | seguridad | feature | bugfix",
    "status": "pending",
    "priority": "high | medium | low",
    "description": "Descripción detallada",
    "acceptanceCriteria": ["Criterio 1", "Criterio 2"],
    "subtasks": [
        {
            "id": "WS-001-1",
            "title": "Subtarea",
            "status": "pending",
            "validation": "comando para verificar"
        }
    ],
    "dependencies": []
}
```

## Convención de IDs

- **WS-XXX**: Web Service (actividades fullstack)
- Subtareas: `WS-XXX-N` (ej: `WS-001-1`, `WS-001-2`)

## Cómo trabajar con las actividades

1. Consultar `activities.json` para ver el estado actual
2. Tomar la siguiente actividad `pending` cuyas dependencias estén `completed`
3. Cambiar estado a `in_progress`
4. Completar subtareas en orden, ejecutando `validation` de cada una
5. Al terminar, cambiar estado a `completed` y hacer commit
