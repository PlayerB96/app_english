# ln1_caja_rapida

Sistema web móvil de caja rápida para puntos de venta en tiendas. Permite escanear códigos de barras, consultar precios y procesar cobros rápidamente.

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
| Base de datos | Microsoft SQL Server (legacy) | driver `sqlsrv` |
| Gestor JS | pnpm | 9+ |
| Lenguaje PHP | PHP | **8.4.21** |
| Lenguaje JS | TypeScript | 5.7+ |

## Requisitos previos

- PHP 8.4.21
- Composer 2.x
- Node.js 20+ y **pnpm**
- Microsoft SQL Server accesible en red (BD legacy del cliente)
- Extensión PHP `pdo_sqlsrv` y ODBC Driver 18 for SQL Server
- Docker (opcional: dev con SQL Server embebido; prod solo app)

## Arquitectura de datos

- El esquema vive en **SQL Server existente** (ej. `DBINFOSAP_ALM`, `DBINFOSAP_B16`). El DBA lo mantiene.
- **No hay migraciones Laravel** ni tablas `users`, `sessions`, `cache` en esa BD.
- Toda la lógica de negocio accede vía **stored procedures** en `app/Repositories/`.
- Detalle: [`database/README.md`](database/README.md).

## Autenticación

| Aspecto | Detalle |
|---------|---------|
| SP | `dbo.usp_movil_valida_usu_pwd_2` |
| Campos login | **Usuario** (`username`, max 20) y **Contraseña** (max 15) |
| Rol permitido | `c_role_codi = 00005` (caja rápida) |
| Sesión | `MobileUser` + `MobileUserProvider` (archivos en `storage/framework/sessions`) |
| Frontend | [`resources/js/Pages/Auth/Login.vue`](resources/js/Pages/Auth/Login.vue) con toggle ver/ocultar clave (Lucide) |

Flujo: `LoginRequest` → `AuthController` → `AuthService` → `AuthRepository` (EXEC SP) → sesión.

## Configuración `.env`

### Conexión SQL Server (legacy)

```env
DB_CONNECTION=sqlsrv
DB_HOST=172.16.0.131      # distinto por servidor en producción
DB_PORT=1433
DB_DATABASE=DBINFOSAP_B16
DB_USERNAME=infosap_user
DB_PASSWORD=
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=true
```

### Infra Laravel (sin tablas en SQL Server)

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

> **Importante:** no uses `SESSION_DRIVER=database` ni `CACHE_STORE=database` contra la BD legacy; esas tablas no existen.

Copia desde [`.env.example`](.env.example). Plantillas por servidor: [`deploy/env/`](deploy/env/).

## Levantar el entorno de desarrollo

### Opción 1: Local contra SQL Server del cliente (recomendado)

```bash
composer install
pnpm install

cp .env.example .env
# Editar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan config:clear

# Terminales separadas:
php artisan serve
pnpm run dev
```

Asegúrate de que existan y sean escribibles:

- `storage/framework/sessions`
- `storage/framework/cache/data`
- `bootstrap/cache`

### Opción 2: Docker (dev con SQL Server embebido)

```bash
cp .env.example .env
docker compose up -d
# Solo para pruebas locales; en producción se usa BD externa legacy
```

> El compose de **desarrollo** incluye SQL Server + Redis. **Producción** usa [`docker-compose.prod.yml`](docker-compose.prod.yml) (solo app, BD externa).

## Estructura del proyecto

```
app/
├── Auth/                   → MobileUserProvider (auth por sesión)
├── Http/Controllers/       → Controladores
├── Http/Requests/          → Form Requests
├── Http/Resources/         → API Resources
├── Http/Middleware/        → Inertia, roles, etc.
├── Models/                 → MobileUser (sesión), User (legacy/tests)
├── Services/               → AuthService, dominio
├── Repositories/           → AuthRepository (SPs), otros repos
└── Enums/                  → MobileRoleCode, UserRole

deploy/
├── deploy.sh               → git pull + rebuild (no modifica .env)
└── env/                    → Plantillas por servidor

resources/js/
├── Pages/Auth/Login.vue    → Login (usuario + contraseña)
├── Layouts/AppLayout.vue
├── types/auth.ts           → Tipos del usuario en sesión
└── ...
```

## Configuración: `.env` y `phpunit.xml`

| Archivo | Objetivo |
|---------|----------|
| **`.env`** | Conexión SQL Server legacy, sesión en archivo, caché local |
| **`phpunit.xml`** | Entorno de tests (`APP_ENV=testing`, sesión `array`, etc.) |

En tests, los repositorios se mockean; no se requiere BD real para auth. Ver `tests/Feature/AuthTest.php`.

## Despliegue multi-servidor (producción)

10 servidores con Docker; mismo código, **`DB_HOST` y `APP_URL` distintos** por nodo.

| Variable | Ámbito |
|----------|--------|
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Compartido |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Compartido (`file` / `file` / `sync`) |
| **`DB_HOST`**, **`APP_URL`** | **Por servidor** |

### Bootstrap (primera vez)

```bash
git clone <repo> /opt/caja_rapida
cd /opt/caja_rapida
cp .env.example .env
# Editar DB_HOST, APP_URL, DB_PASSWORD; php artisan key:generate en el host

docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
```

### Actualizar código (igual en los 10 servidores)

```bash
./deploy/deploy.sh
```

Hace: `git pull` → rebuild Docker → `config:cache`. **No sobrescribe `.env`.**

## Comandos útiles

```bash
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
