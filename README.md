# ln1_caja_rapida

Sistema web móvil de caja rápida para puntos de venta en tiendas. Permite escanear códigos de barras, consultar precios y procesar cobros rápidamente, similar a los sistemas POS de establecimientos como Ripley o Saga.

## Stack tecnológico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | Laravel | 13.11.2 |
| Frontend | Vue 3 (Composition API) | 3.5.x |
| Bridge | Inertia.js | 2.x |
| Estado | Pinia | 3.x |
| Estilos | Tailwind CSS | 3.4.x |
| Bundler | Vite | 6.x |
| Base de datos | Microsoft SQL Server | 2022 (driver `sqlsrv`) |
| Cache | Redis | 7.x |
| Lenguaje PHP | PHP | 8.4.21 |
| Lenguaje JS | TypeScript | 5.7+ |

## Requisitos previos

- PHP 8.4.21
- Composer 2.x
- Node.js 20+ y npm
- Microsoft SQL Server 2019+ (local o Docker)
- Extensión PHP `pdo_sqlsrv` y ODBC Driver 18 for SQL Server
- Redis (o Docker)

## Levantar el entorno de desarrollo

### Opción 1: Con Docker (recomendado)

```bash
# Copiar variables de entorno
cp .env.example .env

# Levantar servicios (app + SQL Server + Redis)
docker compose up -d

# Crear la base de datos (primera vez)
docker compose exec sqlserver /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U sa -P 'YourStrong!Passw0rd' -C \
  -i /scripts/init-database.sql

# Migraciones (desde el contenedor app o en local con .env apuntando a 127.0.0.1:1433)
docker compose exec app php artisan migrate

# La app estará disponible en http://localhost:8000
```

> Ajusta `MSSQL_SA_PASSWORD` en `.env` o en el shell si cambias la contraseña por defecto del compose.

### Opción 2: Local

```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Copiar y configurar variables de entorno
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Levantar servidor de desarrollo (en terminales separadas)
php artisan serve
npm run dev
```

## Estructura del proyecto

```
app/
├── Http/Controllers/      → Controladores (delegan a Services)
├── Http/Requests/          → Form Requests (validación)
├── Http/Resources/         → API Resources (formato de respuesta)
├── Models/                 → Modelos Eloquent
├── Services/               → Lógica de negocio
├── Repositories/           → Acceso a datos (Repository pattern)
└── Enums/                  → Enumeraciones

resources/js/
├── Pages/                  → Páginas Vue (Inertia)
├── Components/             → Componentes reutilizables
├── Composables/            → Composables Vue 3
├── Stores/                 → Pinia (estado global)
├── Layouts/                → Layouts de la app
└── Services/               → Servicios frontend (scanner, etc.)
```

## Configuración: `.env` y `phpunit.xml`

No son alternativas; cumplen roles distintos.

| Archivo | Objetivo |
|---------|----------|
| **`.env`** | Cómo corre la aplicación en desarrollo: SQL Server (`ln1_caja_rapida`), sesión, cola, Redis, etc. |
| **`phpunit.xml`** | Cómo debe comportarse la app **solo mientras ejecutas tests** (`php artisan test` / PHPUnit). |

### Qué hace `phpunit.xml`

1. **Define PHPUnit** — carpetas de tests (`tests/Unit`, `tests/Feature`), cobertura y opciones del runner.
2. **Fija el entorno de prueba** — al correr tests, las variables en `<php><env>…</env></php>` se aplican **después** de `.env` y tienen prioridad en esa ejecución.

Así los tests no dependen del mismo modo que usas en el navegador:

- `APP_ENV=testing`
- Caché y sesión en memoria (`array`), cola `sync`, mail simulado
- Base de datos distinta: `ln1_caja_rapida_test` (no borra datos de `ln1_caja_rapida`)
- Ajustes de rendimiento solo en tests (ej. `BCRYPT_ROUNDS=4`)

La contraseña y credenciales de SQL Server siguen en **`.env`** (o en **`.env.testing`** si la creas); en `phpunit.xml` no hace falta duplicar secretos.

### Opcional: `.env.testing`

Puedes mover ahí toda la configuración de BD de prueba (`DB_*`) y dejar en `phpunit.xml` solo lo específico del runner y del entorno `testing`. Laravel carga `.env.testing` cuando `APP_ENV=testing`.

## Comandos útiles

```bash
# Tests
php artisan test                    # Tests PHP
npm run type-check                  # Verificar tipos TypeScript

# Calidad de código
vendor/bin/pint                     # Formatear PHP (Laravel Pint)
composer analyse                    # Análisis estático (PHPStan)
npm run lint                        # Lint del frontend

# Base de datos
php artisan migrate                 # Ejecutar migraciones
php artisan migrate:fresh --seed    # Resetear BD con seeders
```

## Tracking de actividades

Las actividades de desarrollo se gestionan en `issues/ws/activities.json`. Consultar `issues/README.md` para el protocolo de trabajo.
