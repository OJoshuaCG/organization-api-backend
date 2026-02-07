# Organization API - Documentación Técnica

## 📋 Resumen del Proyecto

API RESTful construida con Laravel 12 para gestión multi-tenant de organizaciones, empresas y usuarios con sistema de autenticación JWT y control de acceso basado en roles (RBAC).

---

## 🏗️ Arquitectura

### Patrones de Diseño Implementados

1. **Repository Pattern**: Separación de lógica de acceso a datos
   - Interfaces en `app/Repositories/Contracts/`
   - Implementaciones en `app/Repositories/Eloquent/`
   - Inyección de dependencias mediante Service Provider

2. **Service Layer**: Lógica de negocio desacoplada
   - Servicios en `app/Services/`
   - Servicios de autenticación en `app/Services/Auth/`

3. **API Resources**: Transformación de respuestas
   - Recursos en `app/Http/Resources/Api/V1/`
   - Incluye relaciones condicionales con `whenLoaded()`

4. **Enums**: Tipado fuerte para valores fijos
   - `UserRole`: Roles de usuario
   - `PermissionType`: Tipos de permisos
   - `TenancyMode`: Modos de tenancy

---

## 🔐 Sistema de Autenticación

### JWT (JSON Web Tokens)

- **Librería**: tymon/jwt-auth
- **Duración**: 60 minutos (configurable en `config/jwt.php`)
- **Refresh**: Soportado
- **Claims personalizados**: role, organization_id

### Flujo de Autenticación

```
POST /api/v1/auth/login
  ↓
Validación de credenciales
  ↓
Generación de JWT
  ↓
Respuesta con access_token, token_type, expires_in
```

### Middleware

- `jwt.auth`: Valida token en header Authorization
- Gestión de excepciones JWT (expirado, inválido, etc.)

---

## 👥 Sistema de Roles y Permisos

### Roles de Usuario (UserRole Enum)

| ID | Rol | Descripción | Acceso WHMCS |
|----|-----|-------------|--------------|
| 1 | SUPER_ADMIN | Control total del sistema | No |
| 2 | ADMIN | Gestión de organizaciones y configuración | No |
| 3 | ORGANIZATION_OWNER | Gestión de su organización y empresas | Sí |
| 4 | COMPANY_OWNER | Gestión de su empresa | Sí |
| 5 | USER | Usuario estándar con acceso limitado | Sí |

### Tipos de Permisos (PermissionType Enum)

| ID | Permiso | Descripción |
|----|---------|-------------|
| 1 | CREATE | Crear recursos |
| 2 | READ | Leer/ver recursos |
| 3 | UPDATE | Actualizar recursos |
| 4 | DELETE | Eliminar recursos |
| 5 | DOWNLOAD | Descargar archivos/datos |

### Matriz de Acceso por Rol

| Recurso | Super Admin | Admin | Org Owner | Company Owner | User |
|---------|-------------|-------|-----------|---------------|------|
| CRUD Organizations (todas) | ✅ | ✅ | ❌ | ❌ | ❌ |
| CRUD Organizations (mi org) | ✅ | ✅ | ✅ | ❌ | ❌ |
| CRUD Companies (todas) | ✅ | ✅ | ❌ | ❌ | ❌ |
| CRUD Companies (mi org) | ✅ | ✅ | ✅ | ❌ | ❌ |
| CRUD Companies (mi empresa) | ✅ | ✅ | ✅ | ✅ | ❌ |
| CRUD Users (todas) | ✅ | ✅ | ❌ | ❌ | ❌ |
| CRUD Users (mi org) | ✅ | ✅ | ✅ | ❌ | ❌ |
| CRUD Users (mi empresa) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Asignar Módulos | ✅ | ✅ | ✅ | ❌ | ❌ |
| Ver Módulos Asignados | ✅ | ✅ | ✅ | ✅ | ✅ |
| Gestionar Permisos | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 🏢 Estructura de Datos

### Jerarquía

```
Organization (Organización)
  └── Company (Empresa)
        └── User (Usuario)
```

### Relaciones Clave

1. **User ↔ Organization**: Un usuario pertenece a una organización
2. **User ↔ Company**: Un usuario puede pertenecer a una empresa específica
3. **User ↔ UsersCompanyAccess**: Un usuario puede tener acceso a múltiples empresas
4. **Company ↔ CompanyModule**: Empresas tienen módulos asignados
5. **User ↔ UsersModulesPermissions**: Usuarios tienen permisos específicos por módulo

---

## 🗄️ Sistema de Tenancy

### Modos de Tenancy

1. **SHARED**: Múltiples empresas comparten la misma base de datos
   - Configurado en `db_shared_connections`
   - Ideal para módulos comunes

2. **DEDICATED**: Cada empresa tiene su propia base de datos
   - Configurado en `db_dedicated_connections`
   - Aislamiento completo de datos

### Configuración

- **TenancyModeGroups**: Definen grupos de conexión
- **ModulesEndpoints**: Cada endpoint tiene asignado un grupo de tenancy
- **Resolución dinámica**: Middleware determina conexión según endpoint

---

## 📊 Modelos y Tablas

### Modelos Principales (con SoftDeletes)

| Modelo | Tabla | Soft Deletes | Auditoría | Descripción |
|--------|-------|--------------|-----------|-------------|
| Organization | organization | ✅ | ✅ | Organizaciones |
| Company | company | ✅ | ✅ | Empresas |
| User | users | ✅ | ❌ | Usuarios |
| Module | modules | ✅ | ✅ | Módulos del sistema |

### Modelos de Relación (sin SoftDeletes)

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| CompanyModule | company_modules | Módulos asignados a empresas |
| UsersCompanyAccess | users_company_access | Acceso de usuarios a empresas |
| UsersModulesPermission | users_modules_permissions | Permisos de usuarios |
| UserTag | user_tags | Tags asignados a usuarios |

### Modelos de Catálogo

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| CatUserRole | cat_user_roles | Roles del sistema |
| CatPermissionType | cat_permission_types | Tipos de permisos |
| TenancyModeGroup | tenancy_mode_groups | Grupos de tenancy |

### Modelos de Infraestructura

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| ModuleEndpoint | modules_endpoints | Endpoints de módulos |
| ModulesEndpointsRequiredRole | modules_endpoints_required_roles | Roles requeridos por endpoint |
| DbSharedConnection | db_shared_connections | Conexiones DB compartidas |
| DbDedicatedConnection | db_dedicated_connections | Conexiones DB dedicadas |
| SessionToken | session_tokens | Tokens de sesión |

---

## 🔒 Seguridad

### Medidas Implementadas

1. **Autenticación**: JWT con expiración
2. **Autorización**: Basada en roles y permisos
3. **Encriptación**: Bcrypt para contraseñas
4. **CORS**: Configurado para dominios específicos
5. **Soft Deletes**: Prevención de pérdida de datos
6. **Validación**: Form requests en todos los endpoints
7. **SQL Injection**: Protección mediante Eloquent
8. **XSS**: Protección mediante escape automático

### Campos Protegidos

- `password`: Hash bcrypt
- `topt_secret`: Oculto en respuestas JSON
- Tokens de sesión: Almacenados con hash

---

## 📝 Endpoints API

### Versionado

Todas las rutas están bajo `/api/v1/`

### Prefijos

- `/api/v1/auth/*`: Autenticación (públicas)
- `/api/v1/admin/*`: Administración (requiere rol Admin+)
- `/api/v1/tenant/*`: Acceso tenant (todos los roles autenticados)

### Respuesta Estándar

```json
{
  "success": true|false,
  "data": { ... },
  "message": "Descripción del resultado",
  "meta": {                      // Solo en listados
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

---

## 🔧 Configuración

### Variables de Entorno Importantes

```env
# Aplicación
APP_NAME="Organization API"
APP_ENV=local
APP_DEBUG=true

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=organization_api
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=valor_generado_automaticamente
JWT_TTL=60

# CORS
CORS_ALLOWED_ORIGINS=*
```

### Archivos de Configuración Clave

- `config/auth.php`: Guards y providers
- `config/jwt.php`: Configuración JWT
- `config/cors.php`: Configuración CORS
- `bootstrap/app.php`: Middleware y providers

---

## 🧪 Testing

### Comandos Disponibles

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=OrganizationTest

# Con cobertura
php artisan test --coverage

# Verbose
php artisan test -v
```

### Estructura de Tests

```
tests/
├── Feature/                    # Tests de integración
│   ├── AuthTest.php
│   ├── OrganizationTest.php
│   └── ...
└── Unit/                       # Tests unitarios
    ├── Services/
    └── Repositories/
```

---

## 📦 Dependencias Principales

### Producción

- `laravel/framework`: ^12.0
- `tymon/jwt-auth`: ^2.2
- `guzzlehttp/guzzle`: ^7.10

### Desarrollo

- `phpunit/phpunit`: ^11.0
- `laravel/pint`: ^1.27
- `mockery/mockery`: ^1.6

---

## 🚀 Deployment

### Requisitos del Servidor

- PHP 8.2+
- MariaDB 10.13+ / MySQL 8.0+
- Composer 2.x
- Extensión PHP: mbstring, pdo_mysql, openssl, tokenizer, xml, ctype, json, bcmath, curl

### Pasos de Producción

1. **Clonar y dependencias**
```bash
git clone <repo>
cd organization-api
composer install --no-dev --optimize-autoloader
```

2. **Configuración**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

3. **Optimización**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. **Base de datos**
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 📞 Soporte

Para dudas o problemas:

1. Revisar logs: `storage/logs/laravel.log`
2. Verificar configuración JWT: `config/jwt.php`
3. Comprobar conexión DB: `php artisan t`
