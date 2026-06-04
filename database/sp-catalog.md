# Catálogo de stored procedures (legacy)

Fuente de verdad de contratos SP antes de implementar módulos de negocio (WS-005+).
El esquema lo mantiene el DBA; la app **no ejecuta DDL** ni migraciones Laravel.

Estados:

| Estado | Significado |
|--------|-------------|
| `ready` | SP disponible y documentado; la app lo usa o puede usarlo |
| `blocked` | Pendiente de entrega o confirmación del DBA; no inventar nombres ni columnas |

## Procedimientos

| SP | Propósito | Parámetros | Columnas de salida | Estado |
|----|-----------|------------|-------------------|--------|
| `dbo.usp_movil_valida_usu_pwd_2` | Validar credenciales de usuario móvil y devolver datos de sesión | `@username` varchar(20), `@password` varchar(15) | `l_exis_usua` (bit/int), `c_usua_codi`, `c_usua_nomb`, `c_codi_empr`, `c_codi_sucu`, `n_tcam_vent`, `c_role_codi`, `c_role_nomb`, `c_nomb_sucu`, `c_sigl_sucu` | `ready` |
| `dbo.usp_*_producto_*` | Consulta de producto por código de barras / SKU | *Pendiente DBA* | *Pendiente DBA* | `blocked` |
| `dbo.usp_*_cobro_*` | Registro de cobro / venta en caja rápida | *Pendiente DBA* | *Pendiente DBA* | `blocked` |

## Notas de integración

### `usp_movil_valida_usu_pwd_2`

- Implementado en `App\Repositories\AuthRepository`.
- Filas mapeadas a `App\DTOs\Auth\MobileUserValidationRowDto`.
- Login permitido si `c_role_codi` es **00005** (Caja Rápida) o **00001** (Administrador); se prioriza **00005** cuando el SP devuelve varias filas.
- Puede devolver **varias filas** (una por rol asignado al usuario).

### SPs futuros (producto / cobro)

- No implementar llamadas hasta que el DBA confirme nombre exacto, parámetros y columnas.
- Actualizar esta tabla cuando el contrato esté acordado.
