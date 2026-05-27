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
| Base de datos | SQLite | 3.x |
| Cache | Redis | 7.x |
| Lenguaje PHP | PHP | 8.4.21 |
| Lenguaje JS | TypeScript | 5.7+ |

## Requisitos previos

- PHP 8.4.21
- Composer 2.x
- Node.js 20+ y npm
- SQLite 3.x (archivo local)
- Redis (o Docker)

## Levantar el entorno de desarrollo

### Opción 1: Con Docker (recomendado)

```bash
# Copiar variables de entorno
cp .env.example .env

# Levantar servicios
docker compose up -d

# La app estará disponible en http://localhost:8000
```

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
