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
- PostgreSQL 14+ (local, Docker o remoto)
- Clave de API de proveedor IA (ej. OpenAI) para generación de preguntas

## Arquitectura

Patrón **layered-repository-service-inertia**:

```
HTTP → Form Request → Controller → Service → Repository → PostgreSQL
                                              ↳ Eloquent (CRUD)
                                              ↳ fn_* / vw_* (functions y views)
                                              ↘ AiQuestionService → API IA
```

- Esquema de **tablas** vía migraciones Laravel.
- **Functions** (`fn_*`) y **views** (`vw_*`) versionadas en `database/sql/` (catálogo en `pg-catalog.md`).
- Auth: `AuthService` → `AuthRepository` (Eloquent + bcrypt).

Detalle de capas: [`.cursor/rules/project.mdc`](.cursor/rules/project.mdc).

## Dominio funcional

| Módulo | Descripción | Estado |
|--------|-------------|--------|
| **Auth** | Login email/password, roles `learner` / `administrator` | Implementado |
| **Learning tracks** | Rutas temáticas (vocabulario dev, entrevistas, docs) | Tablas listas, UI pendiente |
| **Practice** | Sesiones con preguntas generadas por IA | Pendiente (WS-012+) |
| **Voice** | Respuestas habladas transcritas a texto | Pendiente (WS-013) |
| **Progress** | Curva de aprendizaje, nivel, rachas | Pendiente (WS-014) |

## Autenticación

| Aspecto | Detalle |
|---------|---------|
| Login | Email + password contra tabla `users` |
| Flujo | `LoginRequest` → `AuthController` → `AuthService` → `AuthRepository` |
| Roles | `learner` (práctica) · `administrator` (panel `/admin`) |
| Middleware | `role:administrator` en rutas admin |

### Usuarios de prueba (después de `db:seed`)

| Email | Contraseña | Rol |
|-------|------------|-----|
| `learner@app-english.test` | `password` | `learner` |
| `admin@app-english.test` | `password` | `administrator` |

En **modo desarrollo** (`APP_ENV=local` + `APP_DEBUG=true`), el login muestra accesos rápidos para rellenar credenciales.

## Configuración `.env`

### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=app_english
DB_USERNAME=app_english
DB_PASSWORD=secret
```

### Laravel — desarrollo local (recomendado)

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Laravel — producción (mismo servidor que PostgreSQL)

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

### Local (PostgreSQL en Docker o nativo)

```bash
composer install
pnpm install

cp .env.example .env
# Editar DB_* y OPENAI_API_KEY
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan config:clear

# Terminales separadas:
php artisan serve
pnpm run dev
```

### Docker Compose (app + PostgreSQL del proyecto)

```bash
cp .env.example .env
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

## Estructura del proyecto

```
app/
├── Http/Controllers/       → Controladores
├── Http/Requests/          → Form Requests
├── Http/Resources/         → API Resources
├── Models/                 → User (+ modelos de dominio pendientes)
├── Services/               → AuthService, PracticeService (futuro), etc.
├── Repositories/           → AuthRepository, acceso Eloquent / fn_* / vw_*
└── Enums/                  → UserRole

database/
├── migrations/             → 14 tablas (users, tracks, sessions, questions…)
├── sql/functions/          → Functions PostgreSQL (fn_*)
├── sql/views/              → Views PostgreSQL (vw_*)
├── pg-catalog.md           → Catálogo SQL
└── seeders/                → UserSeeder (+ LearningTrackSeeder pendiente)

resources/js/
├── Pages/Auth/Login.vue    → Login + accesos rápidos en dev
├── Pages/Practice/         → Placeholder
├── Layouts/AppLayout.vue
└── types/auth.ts
```

## Comandos útiles

```bash
php artisan migrate
php artisan db:seed
php artisan migrate:status
php artisan db:show

php artisan test
pnpm run type-check
vendor/bin/pint
composer analyse
pnpm run lint
```

## Tracking de actividades

Backlog en `issues/ws/activities.json`. Protocolo: [`issues/README.md`](issues/README.md).

| ID | Estado | Descripción |
|----|--------|-------------|
| WS-010 | Completado | Migraciones PostgreSQL + tablas core |
| WS-011 | Completado | Auth Eloquent + UserSeeder + login dev |
| WS-012 | Pendiente | Integración IA (preguntas) |
| WS-013 | Pendiente | Práctica con voz |
| WS-014 | Pendiente | Curva de aprendizaje (learner) |
| WS-015 | Pendiente | Panel de administración (KPIs, reportes, tracks, usuarios) |

## Estado actual

- **PostgreSQL** con migraciones aplicadas (local o remoto).
- **Auth** por email con `AuthRepository` (Eloquent); roles `learner` / `administrator`.
- **Sin código legacy** del POS (SQL Server / MobileUser eliminado).
- **Pendiente:** modelos Eloquent de dominio, `LearningTrackSeeder`, IA, voz y dashboard de progreso.
