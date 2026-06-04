# Base de datos

El esquema de negocio vive en SQL Server existente (`DBINFOSAP_ALM`). **No se usan migraciones Laravel** para crear tablas.

- Acceso a datos legacy: **stored procedures** encapsulados en `app/Repositories/`.
- Catálogo de SPs y contratos DBA: [`sp-catalog.md`](sp-catalog.md).
- El DBA mantiene el esquema; la app no ejecuta DDL.

Los factories en `database/factories/` se usan solo en tests (objetos en memoria, sin persistir tablas).

Sesión, caché y colas **no** usan tablas Laravel en SQL Server. En `.env` deben ser:

- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`
