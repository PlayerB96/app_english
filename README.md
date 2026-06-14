# app_english

Plataforma de aprendizaje de **inglés para programadores**. Genera preguntas en inglés con IA, sesiones interactivas con voz y seguimiento de la curva de aprendizaje de cada desarrollador.

## Stack tecnológico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | Laravel | 13.11.x |
| Frontend | Vue 3 (Composition API) | 3.5.x |
| Bridge | Inertia.js | 2.x |
| Estado | Pinia | 3.x |
| Estilos | Tailwind CSS | 3.4.x |
| Iconos | @lucide/vue | 1.x |
| Bundler | Vite | 6.x |
| Base de datos | **PostgreSQL** | driver `pgsql` |
| IA | API externa (OpenAI u otro) | vía Services backend |
| Voz | Web Speech API | frontend (composable) |
| Gestor JS | pnpm | 9+ |
| Lenguaje PHP | PHP | **8.4.21** |
| Lenguaje JS | TypeScript | 5.7+ |

## Requisitos previos

- PHP 8.4.21 con extensión `pdo_pgsql`
- Composer 2.x
- Node.js 20+ y **pnpm**
- PostgreSQL 14+ (local o Docker)
- Clave de API de proveedor IA (ej. OpenAI) para generación de preguntas

## Arquitectura

Patrón **layered-repository-service-inertia**:

```
HTTP → Form Request → Controller → Service → Repository → PostgreSQL
                                              ↘ AiQuestionService → API IA
```

- Esquema gestionado con **migraciones Laravel** en PostgreSQL.
- Lógica de negocio e integración IA en `app/Services/`.
- Acceso a datos encapsulado en `app/Repositories/` (Eloquent).
- Voz y captura de respuestas en el frontend; el backend evalúa texto transcrito.

Detalle de capas: [`.cursor/rules/project.mdc`](.cursor/rules/project.mdc).

## Dominio funcional

| Módulo | Descripción |
|--------|-------------|
| **Auth** | Registro/login de developers |
| **Learning tracks** | Rutas temáticas (vocabulario dev, entrevistas, docs) |
| **Practice** | Sesiones con preguntas generadas por IA |
| **Voice** | Respuestas habladas transcritas a texto |
| **Progress** | Curva de aprendizaje, nivel, rachas, precisión |

## Configuración `.env`

### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=app_english
DB_USERNAME=app
DB_PASSWORD=secret
```

### Laravel (sesión, caché, colas)

```env
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Integración IA

```env
AI_PROVIDER=openai
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini
```

Copia desde [`.env.example`](.env.example).

## Levantar el entorno de desarrollo

### Opción 1: Local

```bash
composer install
pnpm install

cp .env.example .env
# Editar DB_* y OPENAI_API_KEY
php artisan key:generate
php artisan migrate
php artisan config:clear

# Terminales separadas:
php artisan serve
pnpm run dev
```

### Opción 2: Docker (PostgreSQL embebido)

```bash
cp .env.example .env
docker compose up -d
docker compose exec app php artisan migrate
```

## Estructura del proyecto

```
app/
├── Http/Controllers/       → Controladores
├── Http/Requests/          → Form Requests
├── Http/Resources/         → API Resources
├── Models/                 → User, Question, PracticeSession, etc.
├── Services/               → PracticeService, AiQuestionService, ProgressService
├── Repositories/           → Acceso Eloquent
└── DTOs/                   → Transferencia de datos

database/
├── migrations/             → Esquema PostgreSQL
└── seeders/                → Tracks y datos demo

resources/js/
├── Pages/                  → Dashboard, Practice, LearningCurve
├── Components/             → QuestionCard, VoiceInput
├── Composables/            → useSpeechRecognition
└── types/                  → TypeScript
```

## Comandos útiles

```bash
# Base de datos
php artisan migrate
php artisan db:seed

# Tests
php artisan test
pnpm run type-check

# Calidad
vendor/bin/pint
composer analyse
pnpm run lint

# Config
php artisan config:clear
```

## Tracking de actividades

Las actividades de desarrollo se gestionan en `issues/ws/activities.json`. Consultar `issues/README.md` para el protocolo de trabajo.

## Estado del pivot

El repositorio conserva la **estructura de capas** del scaffold original (Laravel + Inertia + Vue) pero el dominio cambió de POS/caja rápida a aprendizaje de inglés. El código legacy (`MobileUser`, SP SQL Server, rutas `/pos`) se refactorizará progresivamente según el backlog en `activities.json`.
