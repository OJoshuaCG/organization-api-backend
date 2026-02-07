# Políticas y Reglas del Sistema

## 🔐 Políticas de Autenticación

### JWT Token

1. **Duración**: 60 minutos (3600 segundos)
2. **Refresh**: Permitido antes de expiración
3. **Logout**: Invalidación inmediata del token
4. **Claims requeridos**:
   - `sub`: ID del usuario
   - `role`: ID del rol
   - `organization_id`: ID de organización (puede ser null)

### Reglas de Contraseña

1. **Longitud mínima**: 8 caracteres
2. **Complejidad** (recomendado):
   - Al menos 1 mayúscula
   - Al menos 1 minúscula
   - Al menos 1 número
   - Al menos 1 carácter especial
3. **Almacenamiento**: Hash bcrypt automático
4. **Historial**: No reutilizar últimas 5 contraseñas (pendiente)

### Bloqueo de Cuenta

- **Intentos fallidos**: No implementado (considerar para futuro)
- **Bloqueo temporal**: No implementado
- **Recuperación**: No implementado

---

## 👥 Políticas de Acceso

### Jerarquía de Roles

```
SUPER_ADMIN
    └── ADMIN
        └── ORGANIZATION_OWNER
            └── COMPANY_OWNER
                └── USER
```

### Reglas de Herencia

1. **Super Admin**:
   - Puede hacer todo
   - No pertenece a ninguna organización (organization_id = null)
   - Puede crear otros Super Admins

2. **Admin**:
   - Hereda todo excepto:
     - No puede crear Super Admins
     - No puede eliminar Super Admins
     - No puede modificar otros Admins

3. **Organization Owner**:
   - Solo accede a su organización
   - No puede ver otras organizaciones
   - No puede crear usuarios de rol superior

4. **Company Owner**:
   - Solo accede a su empresa
   - No puede ver otras empresas de la misma organización
   - Solo puede crear usuarios con rol USER

5. **User**:
   - Solo lectura de datos propios
   - No puede crear ni modificar otros usuarios
   - Acceso limitado a módulos asignados

### Reglas de Scope

#### Organizaciones

- **Super Admin + Admin**: CRUD todas las organizaciones
- **Organization Owner**: Solo su organización
- **Company Owner + User**: Solo lectura de su organización

#### Empresas (Companies)

- **Super Admin + Admin**: CRUD todas las empresas
- **Organization Owner**: CRUD empresas de su organización
- **Company Owner**: Solo su empresa (lectura/actualización parcial)
- **User**: Solo lectura de su empresa

#### Usuarios

- **Super Admin**: CRUD todos los usuarios
- **Admin**: CRUD usuarios Admin, Org Owner, Company Owner, User
- **Organization Owner**: CRUD usuarios de su organización
- **Company Owner**: CRUD usuarios de su empresa
- **User**: Solo lectura de su propio perfil

---

## 🛡️ Políticas de Seguridad

### Validación de Datos

1. **Todas las entradas deben ser validadas**
2. **Sanitización**: Usar validadores de Laravel
3. **Mass Assignment**: Usar $fillable en modelos
4. **SQL Injection**: Usar Eloquent/Query Builder (preparado)

### Protección de Datos Sensibles

Campos que NUNCA deben exponerse en respuestas JSON:
- `password`
- `topt_secret`
- `db_pass` (en conexiones)
- `dek_encrypted`

### CORS

Configuración actual:
- `allowed_origins`: `['*']` (cambiar en producción)
- `supports_credentials`: false
- **Producción**: Especificar dominios exactos

### Rate Limiting

**No implementado** - Considerar:
- Login: 5 intentos por minuto
- API general: 1000 requests por hora por usuario
- Endpoints críticos: 100 requests por minuto

---

## 🗄️ Políticas de Base de Datos

### Soft Deletes

**Tablas con Soft Deletes**:
- organization
- company
- users
- modules

**Reglas**:
1. No eliminar físicamente registros
2. Solo Super Admin puede restaurar (considerar implementar)
3. Consultas deben respetar `->withoutTrashed()` o `->withTrashed()` según contexto

### Auditoría

**Campos automáticos**:
- `created_by`: ID del usuario que creó
- `updated_by`: ID del usuario que actualizó
- `created_at`: Timestamp de creación
- `updated_at`: Timestamp de actualización

**Reglas**:
1. Campos se asignan automáticamente vía trait `HasAuditFields`
2. Solo funciona cuando hay usuario autenticado
3. En seeders/datos iniciales: null permitido

### Índices Requeridos

Campos que DEBEN tener índices:
- `users.username` (UNIQUE)
- `users.email` (UNIQUE)
- `users.organization_id` (INDEX)
- `users.user_role_id` (INDEX)
- `company.organization_id` (INDEX)
- `company_modules.company_id` (INDEX)
- `users_company_access.user_id` (INDEX)
- `users_company_access.company_id` (INDEX)

---

## 🔧 Políticas de API

### Versionado

- Versión actual: v1
- Ruta base: `/api/v1/`
- Soporte: Se mantendrá retrocompatibilidad dentro de v1
- Deprecación: Aviso con 6 meses de anticipación

### Respuestas

**Formato estándar**:
```json
{
  "success": true,
  "data": { ... },
  "message": "...",
  "meta": { ... }
}
```

**Códigos HTTP**:
- 200: OK (GET, PUT)
- 201: Created (POST)
- 204: No Content (DELETE)
- 400: Bad Request (validación)
- 401: Unauthorized (no autenticado)
- 403: Forbidden (sin permisos)
- 404: Not Found (recurso no existe)
- 422: Unprocessable Entity (validación fallida)
- 500: Server Error (error interno)

### Paginación

- Default: 15 elementos
- Máximo: 100 elementos
- Parámetro: `per_page`
- Metadata incluida siempre

### Filtros

**Convenciones**:
- Búsqueda por texto: `name` ( LIKE %valor% )
- Estado: `is_active` (boolean)
- IDs: `{model}_id` (integer)
- Orden: `order_by` + `order_direction`

### Headers Requeridos

- `Authorization: Bearer {token}` (endpoints protegidos)
- `Accept: application/json`
- `Content-Type: application/json` (POST/PUT)

---

## 📝 Políticas de Negocio

### Organizaciones

1. **Nombre único**: No implementado (considerar)
2. **Empresas mínimas**: 0 permitido
3. **Usuarios sin organización**: Solo Super Admins
4. **Eliminación**: Cascade soft delete a empresas y usuarios

### Empresas

1. **Obligatorio**: organization_id
2. **Módulos**: Mínimo 0, máximo ilimitado
3. **Usuarios**: Mínimo 1 (Company Owner)
4. **Tags**: Opcionales, ilimitados

### Usuarios

1. **Username**: Único en toda la base de datos
2. **Email**: Único, opcional pero recomendado
3. **Rol obligatorio**: Debe tener un rol asignado
4. **Organización**: Puede ser null solo para Super Admins
5. **Contraseña**: Requerida, mínimo 8 caracteres
6. **Estado**: Inactivo (`is_active = false`) = sin acceso

### Módulos

1. **Nombre único**: Sí
2. **Asignación**: Solo Admin y superiores
3. **Permisos**: CREATE, READ, UPDATE, DELETE, DOWNLOAD
4. **Tenancy**: Cada endpoint tiene modo específico

---

## 🚫 Restricciones Importantes

### Operaciones Prohibidas

1. **No permitido**:
   - Eliminar el único Super Admin
   - Cambiar rol de uno mismo a rol inferior
   - Acceder a datos de otra organización
   - Crear usuarios con rol superior al propio
   - Asignar módulos sin tener permisos

2. **Requiere confirmación** (considerar implementar):
   - Eliminar organización con usuarios activos
   - Cambiar role de usuario existente
   - Desactivar módulos en uso
   - Eliminar cuenta propia

### Validaciones de Negocio

```php
// Ejemplo: No puedes crear un usuario con rol superior al tuyo
if ($newUserRole->value < $currentUser->user_role_id) {
    abort(403, 'No puedes crear usuarios con rol superior');
}

// Ejemplo: No puedes acceder a otra organización
if (!$currentUser->isAdmin() && $resource->organization_id !== $currentUser->organization_id) {
    abort(403, 'No tienes acceso a esta organización');
}
```

---

## 🔄 Políticas de Mantenimiento

### Logs

- **Nivel**: debug en desarrollo, error en producción
- **Rotación**: Archivos diarios
- **Retención**: 30 días
- **Ubicación**: `storage/logs/laravel.log`

### Backups

**No automatizado** - Considerar:
- Base de datos: Diario
- Archivos: Semanal
- Retención: 90 días

### Actualizaciones

- **Laravel**: Seguir versiones LTS
- **Dependencias**: Revisar mensualmente
- **Seguridad**: Aplicar inmediatamente

---

## 📋 Checklist de Implementación

Al agregar nuevas funcionalidades, verificar:

- [ ] Validaciones en Form Requests
- [ ] Autorización en Policies
- [ ] Soft deletes si aplica
- [ ] Auditoría (created_by/updated_by)
- [ ] Tests de feature
- [ ] Documentación en docs/
- [ ] Actualizar AGENT.md
