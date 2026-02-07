# Organization API

API RESTful construida con Laravel 12 para la gestión de organizaciones, empresas y usuarios con sistema de autenticación JWT y control de acceso basado en roles.

## 🚀 Características

- **Autenticación JWT**: Tokens seguros con expiración
- **Control de Roles**: 5 niveles de acceso (Super Admin, Admin, Organization Owner, Company Owner, User)
- **Multi-tenancy**: Soporte para conexiones compartidas y dedicadas
- **Soft Deletes**: Eliminación lógica de registros
- **Auditoría**: Campos created_by y updated_by
- **Repository Pattern**: Código desacoplado y testeable
- **API Resources**: Transformación consistente de respuestas
- **Filtros y Paginación**: Búsqueda avanzada en todos los endpoints

## 📋 Requisitos

- PHP 8.2+
- MariaDB 10.13+
- Composer 2.x

## 🛠️ Instalación

1. **Entrar al proyecto**
```bash
cd organization-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
```

Editar el archivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=organization_api
DB_USERNAME=root
DB_PASSWORD=tu_password
```

4. **Generar claves**
```bash
php artisan key:generate
php artisan jwt:secret
```

5. **Crear base de datos y ejecutar migraciones**
```bash
mysql -u root -p -e "CREATE DATABASE organization_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

6. **Ejecutar seeders**
```bash
php artisan db:seed
```

7. **Iniciar servidor**
```bash
php artisan serve
```

## 🔐 Usuario por Defecto

- **Username**: superadmin
- **Email**: admin@organization.com
- **Password**: SuperAdmin123!

## 📚 Endpoints de la API

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Iniciar sesión |
| POST | `/api/v1/auth/logout` | Cerrar sesión |
| POST | `/api/v1/auth/refresh` | Refrescar token |
| GET | `/api/v1/auth/me` | Obtener usuario actual |

#### Login
```json
POST /api/v1/auth/login
{
    "username": "superadmin",
    "password": "SuperAdmin123!"
}
```

**Respuesta exitosa:**
```json
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "bearer",
        "expires_in": 3600
    },
    "message": "Inicio de sesión exitoso"
}
```

### Organizaciones (Admin)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/v1/admin/organizations` | Listar organizaciones |
| POST | `/api/v1/admin/organizations` | Crear organización |
| GET | `/api/v1/admin/organizations/{id}` | Ver organización |
| PUT | `/api/v1/admin/organizations/{id}` | Actualizar organización |
| DELETE | `/api/v1/admin/organizations/{id}` | Eliminar organización |

#### Filtros disponibles:
- `name`: Buscar por nombre
- `is_active`: Filtrar por estado
- `order_by`: Ordenar por columna
- `order_direction`: asc o desc
- `per_page`: Elementos por página (default: 15)

## 🏗️ Estructura del Proyecto

```
organization-api/
├── app/
│   ├── Enums/                    # Enumeraciones (Roles, Permisos, Tenancy)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       └── V1/
│   │   │           └── Admin/
│   │   │               └── OrganizationController.php
│   │   ├── Middleware/
│   │   │   └── JwtAuthenticate.php
│   │   ├── Resources/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── OrganizationResource.php
│   │   │           ├── CompanyResource.php
│   │   │           └── UserResource.php
│   ├── Models/                   # Modelos Eloquent
│   ├── Providers/
│   │   └── RepositoryServiceProvider.php
│   ├── Repositories/
│   │   ├── Contracts/            # Interfaces
│   │   └── Eloquent/             # Implementaciones
│   ├── Services/                 # Lógica de negocio
│   │   ├── Auth/
│   │   └── OrganizationService.php
│   └── Traits/
│       └── HasAuditFields.php
├── database/
│   ├── sql/                      # Scripts SQL
│   ├── migrations/
│   └── seeders/
└── routes/
    └── api.php                   # Rutas de la API
```

## 🔑 Sistema de Roles

| Rol | ID | Descripción |
|-----|-----|-------------|
| Super Admin | 1 | Control total del sistema |
| Admin | 2 | Gestión de organizaciones |
| Organization Owner | 3 | Gestión de su organización |
| Company Owner | 4 | Gestión de su empresa |
| User | 5 | Usuario estándar |

## 📊 Esquema de Base de Datos

### Tablas Principales
- **organization**: Organizaciones principales
- **company**: Empresas pertenecientes a organizaciones
- **users**: Usuarios del sistema
- **modules**: Módulos disponibles

### Tablas de Relación
- **company_modules**: Módulos asignados a empresas
- **users_company_access**: Acceso de usuarios a empresas
- **users_modules_permissions**: Permisos de usuarios por módulo

### Tablas de Tenancy
- **tenancy_mode_groups**: Grupos de modo tenancy
- **db_shared_connections**: Conexiones compartidas
- **db_dedicated_connections**: Conexiones dedicadas por empresa

## 📝 Notas

- Todos los endpoints protegidos requieren el header `Authorization: Bearer {token}`
- Las contraseñas se almacenan usando bcrypt
- Soporte para soft deletes en tablas principales
- Campos de auditoría (created_by, updated_by) se asignan automáticamente

## 📄 Licencia

Este proyecto es privado y confidencial.
