# Funcionalidades de la API

## 🔐 Módulo de Autenticación

### Login
- **Endpoint**: `POST /api/v1/auth/login`
- **Descripción**: Autenticación de usuarios mediante username/email y password
- **Retorna**: JWT Token con duración configurable
- **Validaciones**:
  - Username requerido
  - Password requerido
  - Usuario activo
  - Credenciales correctas

### Logout
- **Endpoint**: `POST /api/v1/auth/logout`
- **Descripción**: Invalida el token actual
- **Requiere**: Token JWT válido

### Refresh Token
- **Endpoint**: `POST /api/v1/auth/refresh`
- **Descripción**: Genera un nuevo token antes de que expire
- **Requiere**: Token JWT válido

### User Profile
- **Endpoint**: `GET /api/v1/auth/me`
- **Descripción**: Obtiene información del usuario autenticado
- **Retorna**: Datos del usuario, rol, organización, empresa

---

## 🏢 Módulo de Organizaciones (Admin)

### Listar Organizaciones
- **Endpoint**: `GET /api/v1/admin/organizations`
- **Descripción**: Lista paginada de todas las organizaciones
- **Filtros**:
  - `name`: Búsqueda por nombre (LIKE)
  - `is_active`: Filtrar por estado activo/inactivo
  - `order_by`: Campo para ordenar
  - `order_direction`: asc/desc
  - `per_page`: Elementos por página (default: 15)
- **Roles permitidos**: Super Admin, Admin

### Ver Organización
- **Endpoint**: `GET /api/v1/admin/organizations/{id}`
- **Descripción**: Detalle completo de una organización
- **Incluye**: Empresas asociadas, creador, actualizador

### Crear Organización
- **Endpoint**: `POST /api/v1/admin/organizations`
- **Descripción**: Crea nueva organización
- **Campos**:
  - `name`: Nombre (requerido, máx 50)
  - `description`: Descripción (opcional, máx 150)
  - `whmcs_id`: ID de WHMCS (opcional)
  - `is_active`: Estado (default: true)
- **Automático**: created_by, created_at

### Actualizar Organización
- **Endpoint**: `PUT /api/v1/admin/organizations/{id}`
- **Descripción**: Actualiza datos de organización
- **Campos**: Mismos que creación (todos opcionales)
- **Automático**: updated_by, updated_at

### Eliminar Organización
- **Endpoint**: `DELETE /api/v1/admin/organizations/{id}`
- **Descripción**: Soft delete de la organización
- **Cascada**: Elimina empresas, usuarios relacionados

---

## 🏭 Módulo de Empresas (Companies)

### Listar Empresas
- **Rutas**:
  - Admin: `GET /api/v1/admin/companies`
  - Tenant: `GET /api/v1/tenant/companies`
- **Filtros**:
  - `name`: Búsqueda por nombre
  - `organization_id`: Filtrar por organización
  - `is_active`: Estado
- **Scope**:
  - Admin: Todas las empresas
  - Org Owner: Empresas de su organización
  - Company Owner: Solo su empresa

### Ver Empresa
- **Endpoint**: `GET /api/v1/.../companies/{id}`
- **Descripción**: Detalle con módulos asignados, usuarios, tags

### Crear Empresa
- **Endpoint**: `POST /api/v1/.../companies`
- **Descripción**: Crea empresa dentro de una organización
- **Campos**:
  - `organization_id`: ID de organización (requerido)
  - `name`: Nombre (requerido)
  - `description`: Descripción
  - `whmcs_id`: ID de WHMCS
  - `is_active`: Estado

### Actualizar Empresa
- **Endpoint**: `PUT /api/v1/.../companies/{id}`
- **Descripción**: Actualiza datos de la empresa
- **Restricciones**: Solo pueden actualizar Company Owners y superiores

### Eliminar Empresa
- **Endpoint**: `DELETE /api/v1/.../companies/{id}`
- **Descripción**: Soft delete con validación de permisos

### Asignar Módulos
- **Endpoint**: `POST /api/v1/admin/companies/{id}/modules`
- **Descripción**: Asigna módulos disponibles a la empresa
- **Campos**:
  - `module_ids`: Array de IDs de módulos
  - `is_active`: Estado de la asignación

---

## 👤 Módulo de Usuarios

### Listar Usuarios
- **Rutas**:
  - Admin: `GET /api/v1/admin/users`
  - Org: `GET /api/v1/tenant/organizations/{id}/users`
  - Company: `GET /api/v1/tenant/companies/{id}/users`
- **Filtros**:
  - `username`: Búsqueda por username
  - `email`: Búsqueda por email
  - `first_name`: Búsqueda por nombre
  - `last_name`: Búsqueda por apellido
  - `is_active`: Estado
  - `user_role_id`: Filtrar por rol
  - `organization_id`: Por organización
  - `company_id`: Por empresa

### Ver Usuario
- **Endpoint**: `GET /api/v1/.../users/{id}`
- **Descripción**: Perfil completo del usuario
- **Incluye**: Organización, empresa, rol, accesos a empresas, permisos

### Crear Usuario
- **Endpoint**: `POST /api/v1/.../users`
- **Descripción**: Crea nuevo usuario
- **Campos**:
  - `username`: Único (requerido)
  - `email`: Único (opcional)
  - `password`: Mínimo 8 caracteres (requerido)
  - `first_name`: Nombre (opcional)
  - `last_name`: Apellido (opcional)
  - `user_role_id`: Rol (requerido)
  - `organization_id`: Organización
  - `company_id`: Empresa (opcional)
  - `whmcs_id`: ID WHMCS
  - `phone_extension`: Extensión telefónica
  - `is_active`: Estado

### Actualizar Usuario
- **Endpoint**: `PUT /api/v1/.../users/{id}`
- **Descripción**: Actualiza datos del usuario
- **Restricciones**:
  - Super Admin: Puede editar todos
  - Admin: No puede editar Super Admins
  - Org Owner: Solo usuarios de su organización
  - Company Owner: Solo usuarios de su empresa

### Eliminar Usuario
- **Endpoint**: `DELETE /api/v1/.../users/{id}`
- **Descripción**: Soft delete con validación de permisos

### Gestionar Accesos a Empresas
- **Endpoint**: `POST /api/v1/.../users/{id}/company-access`
- **Descripción**: Asigna/quita acceso a empresas
- **Campos**:
  - `company_id`: ID de empresa
  - `enabled`: true/false

### Gestionar Permisos
- **Endpoint**: `POST /api/v1/.../users/{id}/permissions`
- **Descripción**: Asigna permisos sobre módulos
- **Campos**:
  - `module_id`: ID del módulo
  - `permission_id`: Tipo de permiso (1-5)

---

## 🏷️ Módulo de Tags

### Crear Tag
- **Endpoint**: `POST /api/v1/tenant/companies/{id}/tags`
- **Descripción**: Crea tag para usuarios de la empresa
- **Campos**:
  - `name`: Nombre del tag
  - `description`: Descripción
  - `color`: Color HEX
  - `is_active`: Estado

### Asignar Tag a Usuario
- **Endpoint**: `POST /api/v1/tenant/users/{id}/tags`
- **Descripción**: Asigna tag existente a usuario
- **Campos**:
  - `company_user_tag_id`: ID del tag
  - `is_primary`: Tag principal (boolean)

### Listar Tags de Usuario
- **Endpoint**: `GET /api/v1/tenant/users/{id}/tags`
- **Descripción**: Muestra todos los tags asignados al usuario

---

## 📦 Módulo de Módulos

### Listar Módulos
- **Endpoint**: `GET /api/v1/.../modules`
- **Descripción**: Lista módulos disponibles
- **Filtros**: `is_active`, `name`

### Ver Módulo
- **Endpoint**: `GET /api/v1/.../modules/{id}`
- **Descripción**: Detalle con endpoints asociados

### Crear Módulo (Admin)
- **Endpoint**: `POST /api/v1/admin/modules`
- **Descripción**: Crea nuevo módulo del sistema
- **Campos**:
  - `name`: Nombre único
  - `description`: Descripción
  - `is_active`: Estado

### Actualizar Módulo (Admin)
- **Endpoint**: `PUT /api/v1/admin/modules/{id}`

### Desactivar Módulo (Admin)
- **Endpoint**: `DELETE /api/v1/admin/modules/{id}`

---

## ⚙️ Módulo de Configuración

### Perfil de Usuario
- **Endpoint**: `GET /api/v1/tenant/profile`
- **Descripción**: Obtiene perfil del usuario autenticado

### Actualizar Perfil
- **Endpoint**: `PUT /api/v1/tenant/profile`
- **Descripción**: Actualiza datos propios
- **Campos permitidos**:
  - `email`
  - `first_name`
  - `last_name`
  - `phone_extension`
  - `password` (con confirmación)

### Cambiar Contraseña
- **Endpoint**: `POST /api/v1/tenant/profile/change-password`
- **Descripción**: Cambia contraseña propia
- **Campos**:
  - `current_password`: Contraseña actual
  - `new_password`: Nueva contraseña
  - `new_password_confirmation`: Confirmación

---

## 🔍 Funcionalidades Comunes

### Búsqueda Global
- Implementada en todos los listados
- Búsqueda por texto en campos relevantes
- Case-insensitive

### Filtros Avanzados
- Por estado (activo/inactivo)
- Por fecha de creación (rango)
- Por relaciones (organization_id, company_id)
- Por campos específicos

### Ordenamiento
- Campo personalizable
- Dirección ASC/DESC
- Default: created_at DESC

### Paginación
- Configurable por request (`per_page`)
- Default: 15 elementos
- Máximo: 100 elementos
- Metadata completa en respuesta

### Relaciones
- Carga condicional con `with` parameter
- Optimización con eager loading
- Prevención de N+1 queries

---

## 📊 Funcionalidades Pendientes (Roadmap)

### Alta Prioridad
1. [ ] CRUD completo de Empresas
2. [ ] CRUD completo de Usuarios
3. [ ] Sistema de Tags
4. [ ] Gestion de Módulos y Permisos
5. [ ] Validaciones avanzadas con Form Requests

### Media Prioridad
6. [ ] Sistema de logs de auditoría
7. [ ] Exportación de datos (CSV, Excel)
8. [ ] Importación masiva de usuarios
9. [ ] Búsqueda full-text
10. [ ] Cache de consultas frecuentes

### Baja Prioridad
11. [ ] Notificaciones en tiempo real
12. [ ] Dashboard con estadísticas
13. [ ] API de reportes
14. [ ] Webhooks para eventos
15. [ ] Soporte multi-idioma

---

## 🎯 Casos de Uso

### Caso 1: Nuevo Cliente
1. Super Admin crea Organization
2. Admin crea Company dentro de la Organization
3. Org Owner crea usuarios Company Owner
4. Company Owner crea usuarios estándar
5. Se asignan módulos necesarios a la Company

### Caso 2: Gestión de Accesos
1. Admin asigna módulos a Company
2. Org Owner otorga accesos de usuarios a Companies
3. Se asignan permisos específicos (CRUD) por módulo
4. Usuarios acceden según sus permisos

### Caso 3: Organización de Equipos
1. Se crean Tags para departamentos (Ventas, Soporte, etc.)
2. Se asignan Tags a usuarios según su función
3. Se marca tag principal para cada usuario
4. Facilita filtrado y agrupación de usuarios

---

## 📈 Métricas y Monitoreo

### Métricas de API
- Tiempo de respuesta promedio
- Tasa de errores
- Uso de endpoints
- Tokens activos

### Monitoreo
- Logs de acceso
- Logs de errores
- Auditoría de cambios críticos
- Alertas de seguridad

### Reportes
- Usuarios por organización
- Empresas por módulo
- Permisos por usuario
- Actividad reciente
