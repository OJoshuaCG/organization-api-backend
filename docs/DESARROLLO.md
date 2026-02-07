# Guía para Desarrolladores

## 🚀 Inicio Rápido

### 1. Instalación Local

```bash
# Clonar proyecto
git clone <repositorio>
cd organization-api

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Crear base de datos
mysql -u root -p -e "CREATE DATABASE organization_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Migrar y seed
php artisan migrate --seed

# Iniciar servidor
php artisan serve
```

### 2. Credenciales de Prueba

```
Username: superadmin
Password: SuperAdmin123!
```

---

## 🏗️ Estructura de Desarrollo

### Agregar un Nuevo Endpoint

#### Paso 1: Crear Controller
```bash
php artisan make:controller Api/V1/Admin/NewResourceController
```

#### Paso 2: Definir Rutas
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::middleware(['jwt.auth'])->group(function () {
        Route::apiResource('new-resources', NewResourceController::class);
    });
});
```

#### Paso 3: Crear Form Request (Validación)
```bash
php artisan make:request Api/V1/StoreNewResourceRequest
```

```php
class StoreNewResourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
```

#### Paso 4: Crear Resource
```bash
php artisan make:resource Api/V1/NewResourceResource
```

```php
class NewResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

#### Paso 5: Implementar Controller
```php
class NewResourceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private NewResourceRepositoryInterface $repository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $resources = $this->repository->getAll(
            $request->all(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            NewResourceResource::collection($resources),
            'Resources retrieved successfully',
            ['meta' => $this->getPaginationMeta($resources)]
        );
    }

    public function store(StoreNewResourceRequest $request): JsonResponse
    {
        $resource = $this->repository->create($request->validated());

        return $this->successResponse(
            new NewResourceResource($resource),
            'Resource created successfully',
            [],
            201
        );
    }
}
```

### Agregar un Nuevo Modelo

#### Paso 1: Crear Modelo
```bash
php artisan make:model NewModel
```

#### Paso 2: Definir Estructura
```php
class NewModel extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    // Relaciones
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

#### Paso 3: Crear Migración
```bash
php artisan make:migration create_new_models_table
```

```php
Schema::create('new_models', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organization');
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->unsignedInteger('created_by')->nullable();
    $table->unsignedInteger('updated_by')->nullable();
});
```

#### Paso 4: Crear Repository
```php
// Interface
interface NewModelRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?NewModel;
    public function create(array $data): NewModel;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}

// Implementation
class NewModelRepository implements NewModelRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return NewModel::query()->paginate($perPage);
    }

    public function findById(int $id): ?NewModel
    {
        return NewModel::find($id);
    }

    public function create(array $data): NewModel
    {
        return NewModel::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findById($id);
        return $model ? $model->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $model = $this->findById($id);
        return $model ? $model->delete() : false;
    }
}
```

#### Paso 5: Registrar Repository
```php
// app/Providers/RepositoryServiceProvider.php
public function register(): void
{
    $this->app->bind(NewModelRepositoryInterface::class, NewModelRepository::class);
}
```

---

## 🧪 Testing

### Estructura de Tests

```
tests/
├── Feature/
│   ├── AuthTest.php
│   ├── OrganizationTest.php
│   └── CompanyTest.php
└── Unit/
    └── Services/
```

### Crear Test de Feature

```bash
php artisan make:test Feature/OrganizationTest
```

```php
class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
        $this->admin = User::factory()->create(['user_role_id' => UserRole::ADMIN->value]);
    }

    public function test_can_list_organizations(): void
    {
        Organization::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/v1/admin/organizations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'description', 'is_active']
                ],
                'meta'
            ]);
    }

    public function test_can_create_organization(): void
    {
        $data = [
            'name' => 'Test Organization',
            'description' => 'Test Description',
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/admin/organizations', $data);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('organization', $data);
    }
}
```

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=OrganizationTest

# Con verbose
php artisan test -v

# Con cobertura
php artisan test --coverage
```

---

## 🔍 Debugging

### Logs

```php
// Laravel Log
Log::info('Mensaje informativo', ['context' => 'value']);
Log::error('Error message', ['exception' => $e]);

// Depuración rápida
logger($variable);
dd($variable); // Die and dump
```

### Tinker

```bash
php artisan tinker

# Ejemplos
>>> User::first()
>>> Organization::count()
>>> $user = User::find(1)
>>> $user->organizations
```

### Telescope (Opcional)

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

---

## 📚 Recursos Útiles

### Comandos Artisan Útiles

```bash
# Información de rutas
php artisan route:list

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones
php artisan migrate:status
php artisan migrate:rollback
php artisan migrate:fresh --seed

# Modelos
php artisan model:show Organization

# Debug
php artisan tinker
```

### Extensiones VS Code Recomendadas

- PHP Intelephense
- Laravel Extension Pack
- EditorConfig
- ESLint (para frontend)
- Prettier

### Herramientas

- **Postman/Insomnia**: Test de API
- **TablePlus/Sequel Pro**: Gestión de BD
- **Laravel Debugbar**: Debug en desarrollo

---

## 🐛 Troubleshooting

### Problema: Token JWT inválido
**Solución**: Verificar JWT_SECRET en .env, ejecutar `php artisan jwt:secret`

### Problema: CORS errors
**Solución**: Verificar config/cors.php, allowed_origins

### Problema: Migraciones fallan
**Solución**: Verificar orden de creación (dependencias FK), usar `php artisan migrate:fresh`

### Problema: Repositorio no inyectado
**Solución**: Verificar registro en RepositoryServiceProvider

### Problema: Modelo no encontrado
**Solución**: Verificar namespace, nombre de clase, uso de `use`

---

## 💡 Mejores Prácticas

### Código

1. **Principio SOLID**: Single Responsibility, Open/Closed, etc.
2. **DRY**: Don't Repeat Yourself - usar traits, helpers
3. **KISS**: Keep It Simple, Stupid
4. **Type Hinting**: Siempre declarar tipos de parámetros y retornos
5. **DocBlocks**: Documentar métodos complejos

### Laravel

1. **Eloquent**: Preferir sobre Query Builder cuando sea posible
2. **Eager Loading**: Usar `with()` para evitar N+1
3. **Validation**: Usar Form Requests
4. **Authorization**: Usar Policies
5. **API Resources**: Transformar todas las respuestas

### Git

1. Commits frecuentes y atómicos
2. Mensajes descriptivos en español
3. Feature branches: `feature/nombre-funcionalidad`
4. Pull requests con revisión

---

## 📞 Soporte

- **Documentación**: `/docs/`
- **Logs**: `storage/logs/laravel.log`
- **Issues**: Crear ticket en el sistema
- **Slack/Teams**: Canal #dev-organization-api
