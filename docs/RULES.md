# 📋 Manual de Regras de Desenvolvimento - Falcon ERP

> **Objetivo**: Este documento define os padrões arquiteturais, convenções de código e boas práticas para desenvolvimento nos microserviços do ecossistema Falcon ERP.

---

## 🏗️ Arquitetura Geral

### Estrutura de Diretórios

```
app/
├── Http/
│   ├── Controllers/      # Controllers organizados por escopo
│   ├── Middleware/       # Middlewares customizados
│   ├── Requests/         # Form Requests para validação
│   └── Resources/        # API Resources para transformação de dados
├── Jobs/                 # Jobs assíncronos
├── Models/              # Eloquent Models (se houver locais)
├── Providers/           # Service Providers
├── Services/            # Lógica de negócio
│   ├── Erp/
│   │   ├── Private/     # Autenticado
│   │   └── Public/      # Sem autenticação
│   ├── User/            # Contexto do usuário tenant
│   └── BackOffice/      # Administrativo
└── Notifications/       # Notificações
```

### Escopos de API

1. **Erp**: Funcionalidades principais do sistema
   - `Public`: Endpoints sem autenticação (cadastro, login)
   - `Private`: Endpoints autenticados (CRUD de recursos)

2. **User**: Funcionalidades do usuário no contexto tenant
   - Sempre usa middleware `tenant`

3. **BackOffice**: Funcionalidades administrativas
   - Gestão de sistema, dashboards, relatórios

---

## 📁 Convenções de Nomenclatura

### Namespaces

```php
// Services
namespace App\Services\{Escopo}\{Visibilidade}\{Versão};

// Exemplos:
namespace App\Services\Erp\Private\V1;
namespace App\Services\User\Public\V1;
namespace App\Services\BackOffice\Private\V1;

// Controllers
namespace App\Http\Controllers\{Escopo}\{Visibilidade}\{Versão};

// Requests
namespace App\Http\Requests\{Escopo}\{Visibilidade}\{Recurso}\{Entidade};
```

### Classes

```php
// Services: {Entidade}Service
class AuthService extends BaseService { }
class ServiceService extends BaseService { }
class OrderService extends BaseService { }

// Controllers: {Entidade}Controller
class AuthController { }
class ServiceController { }
class OrderController { }

// Requests: {Ação}Request
class StoreRequest extends FormRequest { }
class UpdateRequest extends FormRequest { }
class IndexRequest extends FormRequest { }

// Resources: {Entidade}Resource
class UserResource extends JsonResource { }
class ServiceResource extends JsonResource { }

// Jobs: {Ação}Job
class CheckIpJob implements ShouldQueue { }
class SendEmailJob implements ShouldQueue { }

// Notifications: {Evento}Notification
class UserCreatedNotification extends Notification { }
class SendTokenNotification extends Notification { }
```

---

## 🔧 Padrões de Código

### 1. Services (Camada de Negócio)

**Estrutura Base:**

```php
<?php

declare(strict_types = 1);

namespace App\Services\Erp\Private\V1;

use App\Http\Requests\Erp\Private\Service\Service\IndexRequest;
use App\Http\Requests\Erp\Private\Service\Service\StoreRequest;
use App\Http\Requests\Erp\Private\Service\Service\UpdateRequest;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use QuantumTecnology\ServiceBasicsExtension\BaseService;
use QuantumTecnology\ValidateTrait\Data;

class ServiceService extends BaseService
{
    // Model Eloquent associado
    // Observação: ServiceService tem nome duplicado porque:
    // - Primeiro "Service" = domínio de negócio (prestação de serviço)
    // - Segundo "Service" = padrão arquitetural (Service Pattern)
    protected $model = Service::class;
    
    // Colunas pesquisáveis para filtros SIMPLES (busca por campo único)
    protected array $searchableColumns = [
        'description',
        'value',
    ];
    
    // IMPORTANTE: $initializedAutoDataTrait é uma LISTA DE INCLUSÃO
    // Por padrão, BaseService JÁ INCLUI 'store' e 'update' automaticamente
    // Métodos listados USARÃO validação automática via FormRequest
    
    // ATENÇÃO: Quando você SOBRESCREVE este array na classe filha,
    // você PRECISA ADICIONAR 'store' e 'update' novamente se quiser validação automática
    // OU optar por validação manual (feature flexível do sistema)
    
    protected $initializedAutoDataTrait = [
        // Opção A: Validação manual (caso ServiceService - conflito de nomes)
        'index',       // Usa IndexRequest para validar filtros complexos
        'customAction',// Método customizado que precisa de validação
        // 'store' e 'update' NÃO estão aqui = validação manual
        
        // Opção B: Validação automática (comentado como exemplo)
        // 'store',       // Adicione se quiser validação automática
        // 'update',      // Adicione se quiser validação automática
        // 'index',       // Usa IndexRequest
        // 'customAction',// Método customizado
    ];

    // INDEX com filtros complexos (scopes):
    // - Adicione 'index' em $initializedAutoDataTrait
    // - Crie IndexRequest para validar filtros
    // - Validação automática acontece
    
    public function index(): Data
    {
        // Como está em $initializedAutoDataTrait:
        // - Validação automática via IndexRequest
        // - Dados validados já estão em data()
        // - Útil para filtros com scopes complexos
        return parent::index();
    }
    
    // STORE E UPDATE: Neste caso, optamos por validação MANUAL
    // porque ServiceService tem conflito de nomes (Service + Service)
    // Mas validação manual é uma FEATURE válida do sistema
    
    public function store(): Model
    {
        // Validação manual é flexível e igualmente válida
        data($this->validate(StoreRequest::class));
        return parent::store();
    }
    
    public function update(Model $model): Model
    {
        // Como NÃO está em $initializedAutoDataTrait, valida manualmente
        data($this->validate(UpdateRequest::class));
        return parent::update($model);
    }

    // Métodos customizados SEM entrada do usuário
    // NÃO devem estar em $initializedAutoDataTrait
    public function follow(Service $service): Model
    {
        abort_if(
            $service->followers()->where('follower_people_id', people()->id)->exists(),
            Response::HTTP_BAD_REQUEST,
            __('You are already following this service')
        );
        
        $service->followers()->sync(people()->id);
        
        return $service;
    }
    
    // Métodos customizados COM entrada do usuário
    // DEVEM estar em $initializedAutoDataTrait
    // Assim usam validação automática via FormRequest
    public function customAction(Service $service): Model
    {
        // Como está em $initializedAutoDataTrait:
        // - Crie CustomActionRequest
        // - Validação automática acontece
        // - Dados validados já estão em data()
        
        $data = data(); // Dados validados
        
        // Lógica de negócio
        
        return $service;
    }
}
```

**Regras para Services:**

- ✅ Sempre estenda `BaseService`
- ✅ Defina `$model` com a classe do Model
- ✅ Use tipagem forte em todos os métodos
- ✅ **Importante sobre `$initializedAutoDataTrait`**:
  - BaseService já inclui `'store'` e `'update'` por padrão
  - Quando sobrescrever o array, **readicione** `'store'` e `'update'` se quiser manter validação automática
  - Ou opte por validação manual (feature flexível) - ambas abordagens são válidas
- ✅ **Validação automática via autoData trait**:
  - `$initializedAutoDataTrait` é uma **LISTA DE INCLUSÃO**
  - Métodos listados USAM validação automática via FormRequest
  - Métodos NÃO listados precisam de validação manual com `data($this->validate())`
- ✅ **SEMPRE use FormRequest quando receber dados do usuário** (duas abordagens válidas):
  - **Validação Automática** (adicione no array):
    - `'store'` → adicione em `$initializedAutoDataTrait` + crie `StoreRequest`
    - `'update'` → adicione em `$initializedAutoDataTrait` + crie `UpdateRequest`
  - **Validação Manual** (não adicione no array - feature flexível):
    - `store()` → `data($this->validate(StoreRequest::class))`
    - `update()` → `data($this->validate(UpdateRequest::class))`
  - `index()` com filtros complexos → adicione `'index'` em `$initializedAutoDataTrait` e crie `IndexRequest`
  - Métodos customizados com entrada → adicione no `$initializedAutoDataTrait` e crie FormRequest
- ✅ **Filtros**:
  - Filtros simples (campo único) → use `$searchableColumns`
  - Filtros complexos (scopes múltiplos) → use `IndexRequest`
  - **Notação de filtros**: Por padrão, filtros complexos usam `filter.campo` (ex: `?filter.status=active`)
  - O prefixo `filter` é configurável via `FILTER_PARAMETER` em `config/servicebase.php` (pacote `quantumtecnology/service-base`)
- ✅ Use `abort_if()` / `abort_unless()` para validações de negócio
- ✅ Traduza mensagens com `__()`
- ✅ Use constantes para valores mágicos
- ❌ **Nada deve chegar ao Service sem validação**
- ❌ Não faça queries diretas, use Repositories se necessário
- ❌ Não retorne arrays, use `Data` ou `Model`
- ✅ Use `abort_if()` / `abort_unless()` para validações
- ✅ Traduza mensagens com `__()`
- ✅ Use constantes para valores mágicos
- ❌ Não faça queries diretas, use Repositories se necessário
- ❌ Não retorne arrays, use `Data` ou `Model`

### 2. Form Requests (Validação)

**IMPORTANTE**: Nada deve chegar ao Service sem validação. Sempre que um método receber dados do usuário, crie um FormRequest.

**Casos de uso:**
- `StoreRequest` / `UpdateRequest`: Sempre necessários (validação manual no Service)
- `IndexRequest`: Use quando tiver filtros complexos com scopes (validação automática)
- `CustomActionRequest`: Use para métodos customizados com entrada do usuário (validação automática)

```php
<?php

declare(strict_types = 1);

namespace App\Http\Requests\Erp\Private\Service\Service;

use Illuminate\Foundation\Http\FormRequest;

// Exemplo 1: StoreRequest (validação manual no Service)
class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ou lógica de autorização
    }

    public function rules(): array
    {
        return [
            // Campos obrigatórios
            'description'  => 'required|string|max:255',
            
            // Campos opcionais
            'value'        => 'nullable|numeric|min:0',
            
            // Validação de data/hora
            'service_time' => 'nullable|string|date_format:H:i:s',
            
            // Boolean
            'active'       => 'boolean',
            
            // Text
            'observations' => 'nullable|string|max:1000',
            
            // Arrays
            'shops_id'     => 'sometimes|array',
            'shops_id.*'   => 'integer|exists:FalconERP\Skeleton\Models\Erp\Shop\Shop,id',
            
            // Foreign keys com exists
            'category_id'  => 'required|integer|exists:categories,id',
        ];
    }
    
    // Mensagens customizadas (opcional)
    public function messages(): array
    {
        return [
            'description.required' => __('Description is required'),
            'value.min' => __('Value must be positive'),
        ];
    }
    
    // Atributos customizados (opcional)
    public function attributes(): array
    {
        return [
            'description' => __('Description'),
            'value' => __('Value'),
        ];
    }
}
```

**Exemplo 2: IndexRequest para filtros complexos**

```php
class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Busca simples (searchableColumns já cuida)
            'search' => 'nullable|string',
            
            // Filtros complexos com notação filter.campo
            // Requisição: ?filter.status[]=active&filter.status[]=pending
            'filter.status' => 'nullable|array',
            'filter.status.*' => 'string|in:active,inactive,pending',
            
            // Requisição: ?filter.date_from=2024-01-01&filter.date_to=2024-12-31
            'filter.date_from' => 'nullable|date',
            'filter.date_to' => 'nullable|date|after_or_equal:filter.date_from',
            
            // Requisição: ?filter.category_id=5
            'filter.category_id' => 'nullable|integer|exists:categories,id',
            
            // Paginação
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
    
    // Nota: O prefixo 'filter' vem de config('servicebase.parameters_default.filter')
    // Pode ser customizado via FILTER_PARAMETER no .env
}
```

**Regras para Requests:**

- ✅ **SEMPRE crie FormRequest quando receber dados do usuário**
- ✅ Sempre estenda `FormRequest`
- ✅ Use `authorize()` para lógica de autorização
- ✅ Defina `rules()` com todas as validações
- ✅ Use `nullable` para campos opcionais
- ✅ Use `exists:` para validar foreign keys
- ✅ Use `max:` para limitar tamanho de strings
- ✅ Traduza mensagens com `__()`
- ✅ Organize validações por tipo (required, optional, arrays)
- ✅ Para filtros complexos no index, crie `IndexRequest` com scopes
- ✅ Sempre estenda `FormRequest`
- ✅ Use `authorize()` para lógica de autorização
- ✅ Defina `rules()` com todas as validações
- ✅ Use `nullable` para campos opcionais
- ✅ Use `exists:` para validar foreign keys
- ✅ Use `max:` para limitar tamanho de strings
- ✅ Traduza mensagens com `__()`
- ✅ Organize validações por tipo (required, optional, arrays)

### 3. Controllers

```php
<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Erp\Private\V1;

use App\Services\Erp\Private\V1\ServiceService;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ServiceController
{
    public function __construct(
        protected ServiceService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->service->index()
        );
    }

    public function store(): JsonResponse
    {
        return response()->json(
            $this->service->store(),
            Response::HTTP_CREATED
        );
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function update(Service $service): JsonResponse
    {
        return response()->json(
            $this->service->update($service)
        );
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->service->destroy($service);
        
        return response()->json(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
    
    // Ações customizadas
    public function follow(Service $service): JsonResponse
    {
        return response()->json(
            $this->service->follow($service)
        );
    }
}
```

**Regras para Controllers:**

- ✅ Injete o Service via construtor
- ✅ Controllers devem ser magros (thin controllers)
- ✅ Sempre retorne `JsonResponse`
- ✅ Use HTTP status codes corretos (`Response::HTTP_*`)
- ✅ Delegue toda lógica para o Service
- ❌ Não faça validações no controller
- ❌ Não acesse Models diretamente

### 4. Routes

```php
<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Route;

return fn () => Route::namespace('App\\Http\\Controllers')
    ->middleware([
        'api',
        'encrypt',
        'tenant',
    ])
    ->group(function (): void {
        // Health check
        Route::get('/health', function () {
            return response()->json([
                'ok'   => true,
                'time' => now()->toISOString(),
            ]);
        });

        // Rotas Erp
        Route::namespace('Erp')
            ->name('erp.')
            ->prefix('erp')
            ->group(function (): void {
                
                // Private (autenticado)
                Route::namespace('Private')
                    ->name('private.')
                    ->prefix('private')
                    ->middleware(['auth:sanctum'])
                    ->group(function (): void {
                        
                        Route::prefix('services')
                            ->name('services.')
                            ->group(base_path('routes/erp/private/service.php'));
                    });
                
                // Public (sem autenticação)
                Route::namespace('Public')
                    ->name('public.')
                    ->prefix('public')
                    ->group(function (): void {
                        // Rotas públicas
                    });
            });
    });
```

**Regras para Routes:**

- ✅ Use closure `fn ()` para melhor performance
- ✅ Organize por escopo (Erp, User, BackOffice)
- ✅ Use `->name()` para nomear rotas
- ✅ Use `->prefix()` para prefixar URLs
- ✅ Separe rotas Private e Public
- ✅ Aplique middlewares adequados
- ✅ Extraia grupos grandes para arquivos separados

---

## 🔐 Autenticação e Autorização

### Middlewares

```php
// Autenticação Sanctum
->middleware(['auth:sanctum'])

// Tenant (multi-tenancy)
->middleware(['tenant'])

// Combinados
->middleware(['auth:sanctum', 'tenant'])
```

### Helpers Globais

```php
// Obter people logado
$people = people();
$peopleId = people()->id;

// Obter tenant atual
$tenant = tenant();
$database = tenant();

// Obter usuário autenticado
$user = auth()->user();
```

---

## 🗄️ Banco de Dados

### Models do SDK

**Sempre use os models do FalconERP\Skeleton:**

```php
use FalconERP\Skeleton\Models\User;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use FalconERP\Skeleton\Models\BackOffice\Database;
```

### Schemas PostgreSQL

O sistema usa schemas para organização:

- `people.*` - Dados de pessoas/empresas
- `finance.*` - Dados financeiros
- `stock.*` - Dados de estoque
- `service.*` - Dados de serviços
- `shop.*` - Dados de lojas
- `fiscal.*` - Dados fiscais

### Migrations

```php
// Use schemas quando aplicável
Schema::create('people.types', function (Blueprint $table) {
    $table->id();
    $table->string('description');
    $table->timestamps();
});

// Foreign keys com schema
$table->foreignId('people_id')
    ->constrained('people.peoples')
    ->onDelete('cascade');
```

---

## 🧪 Testes

### Estrutura de Testes

```
tests/
├── Unit/              # Testes unitários
│   ├── Middleware/
│   ├── Services/
│   └── Jobs/
├── Feature/           # Testes de integração
│   ├── Erp/
│   │   ├── Private/
│   │   └── Public/
│   ├── User/
│   └── BackOffice/
└── TestCase.php       # Classe base
```

### Pest PHP

```php
<?php

declare(strict_types=1);

use FalconERP\Skeleton\Models\User;
use Laravel\Sanctum\Sanctum;

// Usando TestCase customizado (se necessário)
// uses(Tests\TestCase::class);

// BeforeEach para setup
beforeEach(function (): void {
    // Setup comum
});

// Teste básico
test('GET /endpoint retorna dados corretamente', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    $response = $this->getJson('/erp/private/services/v1');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'description'],
            ],
        ]);
});

// Teste com factory
test('POST /endpoint cria recurso', function (): void {
    $data = [
        'description' => 'Test Service',
        'value' => 100.00,
    ];
    
    $response = $this->postJson('/erp/private/services/v1', $data);
    
    $response->assertStatus(201);
    
    expect(Service::where('description', 'Test Service')->exists())
        ->toBeTrue();
});

// Teste de validação
test('POST /endpoint valida campos obrigatórios', function (): void {
    $response = $this->postJson('/erp/private/services/v1', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['description']);
});

// Teste com autenticação
test('GET /endpoint requer autenticação', function (): void {
    $response = $this->getJson('/erp/private/services/v1');
    
    $response->assertStatus(401);
});

// Teste marcado como TODO
test('funcionalidade futura', function (): void {
    // Código do teste
})->todo();
```

**Regras para Testes:**

- ✅ Use Pest PHP syntax
- ✅ Nomeie testes de forma descritiva
- ✅ Um teste por funcionalidade
- ✅ Use factories para criar dados
- ✅ Teste casos de sucesso E falha
- ✅ Teste validações
- ✅ Teste autenticação/autorização
- ✅ Use `->todo()` para testes pendentes
- ❌ Não teste framework, teste sua lógica

### Factories

```php
<?php

namespace Database\Factories;

use FalconERP\Skeleton\Models\Erp\Service\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'description' => fake()->sentence(3),
            'value' => fake()->randomFloat(2, 10, 1000),
            'service_time' => fake()->time('H:i:s'),
            'active' => true,
            'observations' => fake()->paragraph(),
        ];
    }
    
    // States
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
```
Filtros e Busca

**Filtros Simples (campo único)**

Use `$searchableColumns` no Service:

```php
protected array $searchableColumns = [
    'description',
    'code',
    'email',
];

// URL: /services?search=keyword
// Busca em description, code e email automaticamente
```

**Filtros Complexos (múltiplos scopes)**

Use `IndexRequest`:

```php
// IndexRequest.php
public function rules(): array
{
    return [
        'status' => 'nullable|array',
        'category_id' => 'nullable|integer',
        'date_from' => 'nullable|date',
    ];
}

// No Model, crie scopes
public function scopeStatus($query, array $status)
{
    return $query->whereIn('status', $status);
}

public function scopeCategory($query, int $categoryId)
{
    return $query->where('category_id', $categoryId);
}

// URL: /services?status[]=active&status[]=pending&category_id=5
```

### 
---

## 🌐 Internacionalização

### Sempre traduza strings visíveis ao usuário:

```php
// ✅ Correto
throw new Exception(__('Service not found'));
abort(404, __('Resource not available'));

// ❌ Errado
throw new Exception('Service not found');
abort(404, 'Resource not available');
```

### Arquivos de tradução:

```
lang/
├── pt_BR/
│   ├── messages.php
│   ├── validation.php
│   └── auth.php
└── en/
    ├── messages.php
    ├── validation.php
    └── auth.php
```

---

## 🚀 Performance e Otimização

### Eager Loading

```php
// ✅ Correto - evita N+1
$services = Service::with(['followers', 'shops'])->get();

// ❌ Errado - causa N+1
$services = Service::all();
foreach ($services as $service) {
    $service->followers; // Query extra!
}
```

### Cache

```php
use Illuminate\Support\Facades\Cache;

// Cache com tempo
Cache::remember('services.active', 3600, function () {
    return Service::where('active', true)->get();
});

// Cache de tag
Cache::tags(['services'])->remember('services.all', 3600, function () {
    return Service::all();
});

// Limpar cache
Cache::tags(['services'])->flush();
```

### Jobs Assíncronos

```php
// Para operações pesadas
CheckIpJob::dispatch($request->ip());
SendEmailJob::dispatch($user, $data)->delay(now()->addMinutes(5));

// Chains
CheckSystemShopJob::withChain([
    new SendNotificationJob($user),
    new UpdateDatabaseJob($data),
])->dispatch();
```

---

## 📝 Documentação de Código

### PHPDoc

```php
/**
 * Cria um novo serviço no sistema
 *
 * @param array $data Dados do serviço
 * @return Service Serviço criado
 * @throws ValidationException Se dados inválidos
 */
public function store(array $data): Service
{
    // Implementação
}

/**
 * @property-read int $id
 * @property string $description
 * @property float $value
 * @property-read Carbon $created_at
 */
class Service extends Model
{
    // Model
}
```

---

## ⚠️ Erros Comuns a Evitar

### 1. ❌ Não usar tipagem
```php
// Errado
public function index()
{
    return $this->service->index();
}

// Correto
public function index(): JsonResponse
{
    return response()->json(
        $this->service->index()
    );
}
```

### 2. ❌ Lógica no Controller
```php
// Errado
public function store(Request $request)
{
    $service = new Service();
    $service->description = $request->description;
    $service->save();
    return response()->json($service);
}

// Correto
public function store(): JsonResponse
{
    return response()->json(
        $this->service->store(),
        Response::HTTP_CREATED
    );
}
```

### 3. ❌ Não validar entrada
```php
// ❌ ERRADO - sem FormRequest
public function update(Service $service)
{
    $service->update(request()->all());
}

// ❌ ERRADO - sem validação
public function update(Service $service): Model
{
    // Sem validação nenhuma!
    return parent::update($service);
}

// ✅ CORRETO - Opção 1: Validação Manual (flexível)
// Útil quando há conflito de nomes ou preferência de controle explícito
protected $initializedAutoDataTrait = [
    'index', // Apenas métodos que precisam de validação automática
    // store e update NÃO estão aqui
];

public function store(): Model
{
    // Validação manual explícita
    data($this->validate(StoreRequest::class));
    return parent::store();
}

public function update(Service $service): Model
{
    // Validação manual explícita
    data($this->validate(UpdateRequest::class));
    return parent::update($service);
}

// ✅ CORRETO - Opção 2: Validação Automática
// IMPORTANTE: BaseService já inclui store/update por padrão
// Mas ao sobrescrever o array, você PRECISA readicionar!
protected $initializedAutoDataTrait = [
    'store',  // Readicionado para validação automática
    'update', // Readicionado para validação automática
    'index',  // Validação automática
];

public function store(): Model
{
    // Validação automática via StoreRequest
    // Dados já estão em data()
    return parent::store();
}

public function update(Service $service): Model
{
    // Validação automática via UpdateRequest
    // Dados já estão em data()
    return parent::update($service);
}

// ✅ CORRETO - método customizado COM entrada
// ADICIONAR em $initializedAutoDataTrait
protected $initializedAutoDataTrait = [
    'customAction', // Usa validação automática
];

public function customAction(Service $service): Model
{
    // Como está em $initializedAutoDataTrait:
    // - Crie CustomActionRequest
    // - Validação automática
    // - Dados já em data()
    
    $data = data(); // Dados validados
    return $service;
}

// ✅ CORRETO - index com filtros complexos
// ADICIONAR em $initializedAutoDataTrait
protected $initializedAutoDataTrait = [
    'index', // Usa IndexRequest
];

public function index(): Data
{
    // Como está em $initializedAutoDataTrait:
    // - Crie IndexRequest
    // - Validação automática
    // - Dados em data()
    return parent::index();
}
```

### 4. ❌ Strings hardcoded
```php
// Errado
abort(404, 'Service not found');

// Correto
abort(404, __('Service not found'));
```

### 5. ❌ Não usar constantes
```php
// Errado
return response()->json($data, 201);

// Correto
return response()->json($data, Response::HTTP_CREATED);
```

---

## 🔄 Versionamento de API

### Estrutura

```
V1/  # Primeira versão
V2/  # Segunda versão (se necessário)
```

### Rotas

```php
// V1
Route::prefix('v1')->group(function () {
    // Endpoints V1
});

// V2
Route::prefix('v2')->group(function () {
    // Endpoints V2 (novos ou modificados)
});
```

---

## 🎯 Checklist de Desenvolvimento

Antes de criar/modificar código, verifique:

- [ ] Namespace está correto?
- [ ] Estende a classe base apropriada?
- [ ] Tipagem forte em todos os métodos?
- [ ] Validação de entrada implementada?
- [ ] Strings traduzidas com `__()`?
- [ ] HTTP status codes corretos?
- [ ] Lógica de negócio no Service?
- [ ] Models do SDK sendo usados?
- [ ] Testes escritos?
- [ ] PHPDoc documentado?
- [ ] Segue PSR-12?

---

## 📚 Recursos Adicionais

### Packages Principais

- **QuantumTecnology/ServiceBasicsExtension**: Base para Services
- **QuantumTecnology/ValidateTrait**: Validação automática
- **QuantumTecnology/PerPageTrait**: Paginação customizável
- **QuantumTecnology/HandlerBasicsExtension**: Exception handler
- **FalconERP/Skeleton**: Models e estruturas compartilhadas
- **Laravel Sanctum**: Autenticação API
- **Laravel Telescope**: Debug e monitoramento
- **Spatie/Prometheus**: Métricas e observabilidade
- **OwenIt/Auditing**: Auditoria de mudanças
- **Pest PHP**: Framework de testes

### Comandos Úteis

```bash
# Testes
php artisan test
php artisan test --filter=ServiceTest

# Code style
./vendor/bin/pint

# Análise estática
./vendor/bin/phpstan analyse

# Migrations
php artisan migrate
php artisan migrate:fresh --seed
```
---

## 🔍 Exemplos Práticos

### Criar um novo recurso completo

1. **Model** (use do SDK)
```php
use FalconERP\Skeleton\Models\Erp\Service\Service;
```

2. **Requests**
```php
// StoreRequest.php
// UpdateRequest.php
// IndexRequest.php (se tiver filtros complexos)
```

3. **Service**
```php
class ServiceService extends BaseService
{
    protected $model = Service::class;
    
    protected array $searchableColumns = ['description'];
    
    // LISTA DE INCLUSÃO para validação automática
    protected $initializedAutoDataTrait = [
        'index',       // Se tiver filtros complexos
        'customAction',// Métodos customizados com entrada
    ];
    
    public function store(): Model
    {
        data($this->validate(StoreRequest::class));
        return parent::store();
    }
    
    public function update(Service $service): Model
    {
        data($this->validate(UpdateRequest::class));
        return parent::update($service);
    }
    
    public function index(): Data
    {
        // Validação automática via IndexRequest
        return parent::index();
    }
    
    public function follow(Service $service): Model
    {
        // Sem entrada do usuário
        $service->followers()->sync(people()->id);
        return $service;
    }
}
```

4. **Controller**
```php
class ServiceController
{
    public function __construct(
        protected ServiceService $service
    ) {}
}
```

5. **Routes**
```php
Route::apiResource('services', ServiceController::class);
```

6. **Tests**
```php
test('lista serviços', function () {
    // Teste aqui
});
```

---

## � Estrutura Padrão de Pastas

Todos os microserviços devem seguir esta estrutura:

```
app/
├── Enums/              # Enumerações (StatusEnum, TypeEnum, etc)
├── Events/             # Eventos (Event/Listener pattern)
├── Facades/            # Facades customizadas
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Jobs/               # Filas e background jobs
├── Listeners/          # Event listeners
├── Models/             # Models locais (prefira FalconERP/Skeleton)
├── Policies/           # Policies de autorização
├── Providers/
│   ├── AppServiceProvider.php
│   ├── EventServiceProvider.php
│   └── PrometheusServiceProvider.php
├── Repositories/       # Repositories para lógica complexa de query
├── Rules/              # Validation rules customizadas
├── Services/           # Services (camada de negócio)
└── Traits/             # Traits reutilizáveis

config/
├── app.php
├── audit.php           # Configuração de auditoria (OwenIt)
├── auth.php
├── cache.php
├── cors.php
├── database.php
├── filesystems.php
├── hashids.php
├── logging.php
├── mail.php
├── perpage.php         # Configuração de paginação
├── prometheus.php      # Métricas e observabilidade
├── queue.php
├── sanctum.php         # Configuração Sanctum
├── services.php
├── session.php
└── telescope.php       # Debug e monitoramento
```

**Padrões de Estrutura:**
- ✅ Crie pastas mesmo vazias (facilita padrão consistente)
- ✅ Use Enums para valores fixos (Status, Type, etc)
- ✅ Repositories para queries complexas
- ✅ Traits para código reutilizável entre classes
- ✅ Facades para APIs fluentes
- ✅ Policies para autorização (Gate/Can)
- ✅ Events/Listeners para ações assíncronas

---

## 🔧 Configurações Padrão

### AppServiceProvider

```php
use Illuminate\Auth\RequestGuard;

public function register(): void
{
    $this->app->singleton(ExceptionHandler::class, BaseHandler::class);

    // Credentials macro
    RequestGuard::macro('is_master', fn () => static::user()->is_master);

    $this->configureTelescope();
}

public function boot(): void
{
    Model::preventLazyLoading(!app()->isProduction());
}
```

### PrometheusServiceProvider

```php
public function boot(): void
{
    Prometheus::addGauge('User count')
        ->helpText('This is the number of users in our app')
        ->value(fn () => User::count());
        
    // Adicione métricas relevantes do seu microserviço
}
```

### EventServiceProvider

```php
protected $listen = [
    YourEvent::class => [
        YourListener::class,
    ],
];
```

---

## 📌 Conclusão

Este manual é um guia vivo. Sempre que identificar novos padrões ou melhores práticas, atualize este documento. A consistência no código facilita manutenção, colaboração e escalabilidade do sistema.

**Checklist de Conformidade com Padrão:**
- [ ] Estrutura de pastas completa (Enums, Events, Listeners, Repositories, Traits, Facades, Rules, Policies)
- [ ] AppServiceProvider com `RequestGuard::macro('is_master')`
- [ ] Configs: telescope.php, audit.php, prometheus.php, sanctum.php, perpage.php
- [ ] PrometheusServiceProvider configurado com métricas
- [ ] EventServiceProvider com listeners registrados
- [ ] Model::preventLazyLoading(!app()->isProduction())

**Lembre-se**: Código limpo é código que outros desenvolvedores (ou você no futuro) conseguem entender facilmente.

---

**Última atualização**: 13/12/2025  
**Versão**: 1.0.0
