# Agent Context - Organization API

> **INSTRUCCIONES PARA IA**: Este archivo contiene el contexto completo del proyecto. LÉELO COMPLETAMENTE antes de hacer cualquier cambio.

---

## 🎯 Visión General

**Organization API** es una API RESTful construida con **Laravel 12** para gestión multi-tenant de organizaciones, empresas y usuarios. Incluye autenticación JWT, control de acceso basado en roles (RBAC), soft deletes, auditoría completa y sistema de tenancy.

---

## 🏗️ Arquitectura del Proyecto

### Tecnologías Principales

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de Datos**: MariaDB 10.13+
- **Autenticación**: JWT (tymon/jwt-auth)
- **Patrón**: Repository Pattern + Service Layer

### Estructura de Carpetas

```
organization-api/
├── app/
│   ├── Enums/                    # PHP 8.1 Enums
│   ├── Http/
│   │   ├── Controllers/Api/      # Controllers
│   │   ├── Middleware/           # JWT Auth, CORS
│   │   └── Resources/Api/V1/     # API Resources
│   ├── Models/                   # 16 modelos Eloquent
│   ├── Providers/                # Service Providers
│   ├── Repositories/             # Repository Pattern
│   │   ├── Contracts/            # Interfaces
│   │   └── Eloquent/             # Implementaciones
│   ├── Services/                 # Lógica de negocio
│   └── Traits/                   # HasAuditFields
├── database/
│   ├── sql/                      # Scripts SQL originales
│   ├── migrations/               # Migraciones Laravel
│   └── seeders/                  # Datos iniciales
├── docs/                         # DOCUMENTACIÓN COMPLETA
├── routes/
│   └── api.php                   # Rutas API v1
└── config/                       # Configuraciones
```

---

## 🔐 Sistema de Autenticación

### JWT (JSON Web Tokens)

- **Duración**: 60 minutos
- **Claims incluidos**: `role`, `organization_id`
- **Endpoints**:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `POST /api/v1/auth/refresh`
  - `GET /api/v1/auth/me`

### Middleware

- `jwt.auth`: Valida token JWT
- Aplicado en rutas protegidas

---

## 👥 Sistema de Roles

### UserRole Enum (IDs fijos)

```php
1 => SUPER_ADMIN      // Control total
2 => ADMIN            // Gestión de organizaciones
3 => ORGANIZATION_OWNER // Su organización
4 => COMPANY_OWNER    // Su empresa
5 => USER             // Acceso limitado
```

### PermissionType Enum

```php
1 => CREATE
2 => READ
3 => UPDATE
4 => DELETE
5 => DOWNLOAD
```

### Jerarquía de Acceso

- **Super Admin**: Todo el sistema
- **Admin**: Todo excepto Super Admins
- **Organization Owner**: Su organización y empresas
- **Company Owner**: Su empresa
- **User**: Solo lectura de sus datos

---

## 🗄️ Modelos Principales

### Con SoftDeletes + Auditoría

- **Organization**: Organizaciones principales
- **Company**: Empresas (pertenecen a Organization)
- **Module**: Módulos del sistema

### Solo Relaciones

- **User**: Usuarios (implementa JWTSubject)
- **CompanyModule**: Módulos asignados a empresas
- **UsersCompanyAccess**: Acceso de usuarios a empresas
- **UsersModulesPermission**: Permisos específicos

### Catálogos

- **CatUserRole**: Roles (id fijos 1-5)
- **CatPermissionType**: Permisos (id fijos 1-5)
- **TenancyModeGroup**: Grupos de tenancy

---

## 🏢 Estructura de Datos

```
Organization
  └── hasMany: Company[]
        └── hasMany: User[]
              └── belongsToMany: Company[] (via UsersCompanyAccess)
              └── hasMany: UsersModulesPermission[]
```

### Relaciones Clave

- **User** pertenece a una **Organization**
- **User** puede tener acceso a múltiples **Companies**
- **Company** pertenece a una **Organization**
- **Module** puede asignarse a múltiples **Companies**

---

## 🔄 Repository Pattern

### Interfaces (Contracts)

- `OrganizationRepositoryInterface`
- `CompanyRepositoryInterface`
- `UserRepositoryInterface`

### Implementaciones (Eloquent)

- `OrganizationRepository`
- `CompanyRepository`
- `UserRepository`

### Registro

En `RepositoryServiceProvider::register()`:
```php
$this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
```

---

## 📊 Endpoints Implementados

### Autenticación (Público)

- `POST /api/v1/auth/login`

### Admin (Requiere: Admin+)

- `GET /api/v1/admin/organizations` - Listar
- `POST /api/v1/admin/organizations` - Crear
- `GET /api/v1/admin/organizations/{id}` - Ver
- `PUT /api/v1/admin/organizations/{id}` - Actualizar
- `DELETE /api/v1/admin/organizations/{id}` - Eliminar

### Filtros Disponibles (GET)

- `name`: Búsqueda por nombre
- `is_active`: true/false
- `order_by`: Campo ordenamiento
- `order_direction`: asc/desc
- `per_page`: Elementos por página (default: 15)

---

## 🛡️ Seguridad

### Medidas Implementadas

1. **JWT**: Tokens con expiración
2. **Bcrypt**: Hash de contraseñas
3. **Soft Deletes**: Eliminación lógica
4. **CORS**: Configurado para frontend
5. **Validación**: Form requests
6. **Auditoría**: created_by, updated_by automáticos

### Campos Protegidos

- `password` - Nunca en respuestas
- `topt_secret` - Nunca en respuestas
- `db_pass` - En conexiones

---

## 🧪 Testing

### Seeders Creados

1. **CatalogSeeder**: Roles, permisos, tenancy groups
2. **SuperAdminSeeder**: Usuario por defecto

### Usuario por Defecto

```
Username: superadmin
Email: admin@organization.com
Password: SuperAdmin123!
Role: Super Admin (1)
```

---

## 📚 Documentación

### Archivos en /docs/

1. **README.md**: Documentación técnica completa
2. **FUNCIONALIDADES.md**: Lista de funcionalidades
3. **POLITICAS.md**: Políticas y reglas del sistema
4. **DESARROLLO.md**: Guía para desarrolladores

---

## 🚀 Comandos Importantes

### Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

### Testing

```bash
php artisan test
php artisan test --filter=OrganizationTest
```

### Debugging

```bash
php artisan tinker
tail -f storage/logs/laravel.log
```

---

## ⚠️ Reglas Críticas para IA

### Al Agregar Código

1. **SIEMPRE** usar Repository Pattern
2. **SIEMPRE** crear Resource para respuestas API
3. **SIEMPRE** validar con Form Requests
4. **SIEMPRE** respetar jerarquía de roles
5. **SIEMPRE** incluir soft deletes en tablas principales
6. **SIEMPRE** usar tipo de retorno en métodos

### NUNCA

- Exponer `password` o `topt_secret` en JSON
- Eliminar físicamente registros (usar soft delete)
- Crear usuarios con rol superior al creador
- Ignorar validaciones de negocio

### Convenciones de Nombre

- **Controllers**: PascalCase, suffix `Controller`
- **Models**: PascalCase, singular
- **Tables**: snake_case, plural
- **Routes**: kebab-case
- **Variables**: camelCase

---

## 🔧 Configuración

### Variables .env Críticas

```env
DB_CONNECTION=mysql
DB_DATABASE=organization_api
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=valor_generado
JWT_TTL=60
```

### Archivos de Config

- `config/auth.php`: Guards (api -> jwt)
- `config/jwt.php`: Configuración JWT
- `config/cors.php`: Orígenes permitidos
- `bootstrap/app.php`: Middleware registration

---

## 📝 Notas para Mantenimiento

### Cuando Agregues:

- **Nuevo Modelo**: Extender BaseModel, usar HasAuditFields
- **Nueva Migración**: Considerar softDeletes y created_by/updated_by
- **Nuevo Controller**: Inyectar Repository, usar Resources
- **Nuevo Endpoint**: Agregar a `routes/api.php`, proteger con middleware
- **Nuevo Enum**: Crear en `app/Enums/`, documentar casos

### Dependencias Principales

```json
{
  "laravel/framework": "^12.0",
  "tymon/jwt-auth": "^2.2"
}
```

---

## 🐛 Troubleshooting Común

### Token inválido
```bash
php artisan jwt:secret
```

### Errores CORS
Verificar `config/cors.php` - allowed_origins

### Repository no inyectado
Verificar registro en `RepositoryServiceProvider`

### Migraciones fallan
Verificar orden (dependencias FK), usar `php artisan migrate:fresh`

---

## 💡 Contexto de Negocio

### Flujo Principal

1. Super Admin crea **Organization**
2. Admin/Org Owner crea **Companies** dentro de la organización
3. Se asignan **Modules** a las companies
4. Se crean **Users** con roles específicos
5. Se otorgan **permisos** por módulo a usuarios
6. Se gestionan **accesos** a companies

### Multi-Tenancy

- **Shared**: Múltiples empresas en misma BD
- **Dedicated**: Cada empresa con BD propia
- Configurado por `tenancy_mode_group_id` en endpoints

---

## 📞 Contacto y Soporte

- **Documentación**: `/docs/` (4 archivos markdown)
- **Logs**: `storage/logs/laravel.log`
- **BD**: MariaDB 10.13, nombre: `organization_api`

---

> **RECUERDA**: Siempre revisa los archivos en `/docs/` antes de implementar funcionalidades nuevas. Mantén la consistencia con el código existente.

> **Última actualización**: 2026-02-07
> **Versión Laravel**: 12.x
> **Autor**: Claude Code (AI Assistant)
