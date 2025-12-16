# 📋 Manual de Regras de Desenvolvimento - Laravel

> **Objetivo**: Este documento define os padrões arquiteturais, convenções de código e boas práticas para desenvolvimento em projetos Laravel seguindo Clean Architecture e Service Layer Pattern.

**Autor**: Luis Gustavo Santarosa Pinto  
**Email**: gustavo-computacao@hotmail.com

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

1. **Api**: Funcionalidades principais do sistema
   - `Public`: Endpoints sem autenticação (cadastro, login)
   - `Private`: Endpoints autenticados (CRUD de recursos)

2. **Admin**: Funcionalidades administrativas
   - Gestão de sistema, dashboards, relatórios

3. **User**: Funcionalidades do contexto do usuário
   - Perfil, preferências, notificações

---

## 📁 Convenções de Nomenclatura

### Namespaces

```php
// Services
namespace App\Services\{Escopo}\{Visibilidade}\{Versão};

// Exemplos:
namespace App\Services\Api\Private\V1;
namespace App\Services\Api\Public\V1;
namespace App\Services\Admin\Private\V1;

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

namespace App\Services\Api\Private\V1;

use App\Http\Requests\Api\Private\Product\IndexRequest;
use App\Http\Requests\Api\Private\Product\StoreRequest;
use App\Http\Requests\Api\Private\Product\UpdateRequest;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use QuantumTecnology\ServiceBasicsExtension\BaseService;
use QuantumTecnology\ValidateTrait\Data;

class ProductService extends BaseService
{
    // Model Eloquent associado
    protected $model = Product::class;
    
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
        // Opção A: Validação manual
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
    public function activate(Product $product): Model
    {
        abort_if(
            $product->active,
            Response::HTTP_BAD_REQUEST,
            __('Product is already active')
        );
        
        $product->update(['active' => true]);
        
        return $product;
    }
    
    // Métodos customizados COM entrada do usuário
    // DEVEM estar em $initializedAutoDataTrait
    // Assim usam validação automática via FormRequest
    public function customAction(Product $product): Model
    {
        // Como está em $initializedAutoDataTrait:
        // - Crie CustomActionRequest
        // - Validação automática acontece
        // - Dados validados já estão em data()
        
        $data = data(); // Dados validados
        
        // Lógica de negócio
        
        return $product;
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
- ✅ Use `abort_if()` / `abort_unless()` para validações (ou prefira Policies com `Gate::inspect()`)
- ✅ Traduza mensagens com `__()`
- ✅ Use constantes para valores mágicos

#### Hooks do Ciclo de Vida (Lifecycle Hooks)

O `BaseService` fornece hooks que são executados automaticamente durante operações CRUD. Use-os para adicionar lógica antes/depois de criar ou atualizar registros:

**Hooks disponíveis:**
- `storing(): void` - Antes de criar (store)
- `stored($model)` - Depois de criar (store) - **DEVE retornar o model**
- `updating(): void` - Antes de atualizar (update)
- `updated($model)` - Depois de atualizar (update) - **DEVE retornar o model**
- `deleting($model): void` - Antes de deletar (destroy)
- `deleted($model): void` - Depois de deletar (destroy)

**Exemplo prático:**

```php
class FaqTopicService extends BaseService
{
    protected string $model = FaqTopic::class;
    
    protected array $initializedAutoDataTrait = [
        'store',
        'update',
    ];
    
    // Hook: preparar dados ANTES de criar
    protected function storing(): void
    {
        data()->merge([
            'user_id' => auth()->id(),
            'status' => data('status', 'open'),
        ]);
    }
    
    // Hook: executar lógica DEPOIS de criar
    // IMPORTANTE: Não tipar o parâmetro para compatibilidade com BaseService
    // IMPORTANTE: DEVE retornar o model
    protected function stored($topic)
    {
        // Incrementar contador de tópicos do usuário
        $this->updateUserLevel($topic->user_id, 'incrementTopics');
        $this->addExperience($topic->user_id, 5);
        
        // Enviar notificação
        event(new TopicCreated($topic));
        
        return $topic;
    }
    
    // Hook: preparar dados ANTES de atualizar
    protected function updating(): void
    {
        data()->merge([
            'updated_by' => auth()->id(),
        ]);
    }
    
    // Hook: executar lógica DEPOIS de atualizar
    // IMPORTANTE: Não tipar o parâmetro para compatibilidade com BaseService
    // IMPORTANTE: DEVE retornar o model
    protected function updated($topic)
    {
        // Invalidar cache
        Cache::forget("topic.{$topic->id}");
        
        // Registrar auditoria
        Log::info("Topic {$topic->id} updated by " . auth()->id());
        
        return $topic;
    }
}
```

**Regras para Hooks:**

- ✅ Use `storing()` e `updating()` para preparar/modificar dados antes de salvar
- ✅ Use `stored()` e `updated()` para lógica pós-salvamento (notificações, cache, contadores)
- ✅ Hooks `storing()` e `updating()` são `void` - não retornam nada
- ✅ Hooks `stored()` e `updated()` **DEVEM retornar o model**
- ✅ Hooks `deleting()` e `deleted()` são `void` - não retornam nada
- ✅ Use `data()->merge([])` para adicionar/modificar dados nos hooks `storing()` e `updating()`
- ✅ Use `data('campo', 'default')` para obter valores com fallback
- ✅ **Não tipar parâmetros dos hooks** para compatibilidade com BaseService
- ✅ **NUNCA sobrescreva `store()` ou `update()` apenas para adicionar dados** - use hooks
- ✅ Sobrescreva `store()` ou `update()` apenas se precisar de lógica complexa que não se encaixa nos hooks
- ❌ Não faça queries pesadas em hooks (use Jobs/Queues se necessário)
- ❌ Não lance exceptions em hooks `stored/updated` - o registro já foi salvo

### 2. Form Requests (Validação)

**IMPORTANTE**: Nada deve chegar ao Service sem validação. Sempre que um método receber dados do usuário, crie um FormRequest.

**Casos de uso:**
- `StoreRequest` / `UpdateRequest`: Sempre necessários (validação manual no Service)
- `IndexRequest`: Use quando tiver filtros complexos com scopes (validação automática)
- `CustomActionRequest`: Use para métodos customizados com entrada do usuário (validação automática)

```php
<?php

declare(strict_types = 1);

namespace App\Http\Requests\Api\Private\Product;

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
            'name'         => 'required|string|max:255',
            'sku'          => 'required|string|max:100|unique:products,sku',
            
            // Campos opcionais
            'price'        => 'nullable|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            
            // Validação de data
            'expires_at'   => 'nullable|date|after:today',
            
            // Boolean
            'active'       => 'boolean',
            
            // Text
            'description'  => 'nullable|string|max:1000',
            
            // Arrays
            'tags'         => 'sometimes|array',
            'tags.*'       => 'string|max:50',
            
            // Foreign keys com exists
            'category_id'  => 'required|integer|exists:categories,id',
        ];
    }
    
    // Mensagens customizadas (opcional)
    public function messages(): array
    {
        return [
            'name.required' => __('Product name is required'),
            'price.min' => __('Price must be positive'),
        ];
    }
    
    // Atributos customizados (opcional)
    public function attributes(): array
    {
        return [
            'name' => __('Product Name'),
            'price' => __('Price'),
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

namespace App\Http\Controllers\Api\Private\V1;

use App\Services\Api\Private\V1\ProductService;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController
{
    public function __construct(
        protected ProductService $service
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

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function update(Product $product): JsonResponse
    {
        return response()->json(
            $this->service->update($product)
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->service->destroy($product);
        
        return response()->json(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
    
    // Ações customizadas
    public function activate(Product $product): JsonResponse
    {
        return response()->json(
            $this->service->activate($product)
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
    ])
    ->group(function (): void {
        // Health check
        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'time'   => now()->toISOString(),
            ]);
        });

        // Rotas Api
        Route::namespace('Api')
            ->name('api.')
            ->prefix('api')
            ->group(function (): void {
                
                // Private (autenticado)
                Route::namespace('Private')
                    ->name('private.')
                    ->prefix('private')
                    ->middleware(['auth:sanctum'])
                    ->group(function (): void {
                        
                        Route::prefix('products')
                            ->name('products.')
                            ->group(base_path('routes/api/private/products.php'));
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
// Obter usuário autenticado
$user = auth()->user();
$userId = auth()->id();

// Obter tenant atual (se usar multi-tenancy)
$tenant = tenant();

// Helper customizado (exemplo)
$currentCompany = currentCompany();
```

---

## 🗄️ Banco de Dados

### Models

**Organize seus models por contexto:**

```php
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

### Schemas PostgreSQL (Opcional)

Se usar PostgreSQL com schemas para organização:

- `auth.*` - Autenticação e usuários
- `core.*` - Funcionalidades principais
- `billing.*` - Faturamento
- `analytics.*` - Relatórios e métricas

### Migrations

```php
// Exemplo básico
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->timestamps();
});

// Foreign keys
$table->foreignId('category_id')
    ->constrained('categories')
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
│   ├── Api/
│   │   ├── Private/
│   │   └── Public/
│   ├── Admin/
│   └── User/
└── TestCase.php       # Classe base
```

### Pest PHP

```php
<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Product;
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
    
    $response = $this->getJson('/api/private/products/v1');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price'],
            ],
        ]);
});

// Teste com factory
test('POST /endpoint cria recurso', function (): void {
    $data = [
        'name' => 'Test Product',
        'price' => 99.99,
    ];
    
    $response = $this->postJson('/api/private/products/v1', $data);
    
    $response->assertStatus(201);
    
    expect(Product::where('name', 'Test Product')->exists())
        ->toBeTrue();
});

// Teste de validação
test('POST /endpoint valida campos obrigatórios', function (): void {
    $response = $this->postJson('/api/private/products/v1', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

// Teste com autenticação
test('GET /endpoint requer autenticação', function (): void {
    $response = $this->getJson('/api/private/products/v1');
    
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

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => fake()->unique()->bothify('SKU-####-??'),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 100),
            'active' => true,
            'description' => fake()->paragraph(),
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

// URL: /products?search=keyword
// Busca em name, sku e description automaticamente
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

// URL: /products?status[]=active&status[]=pending&category_id=5
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

### Computed Attributes (Atributos Calculados)

Use computed attributes para adicionar dados calculados em tempo real aos seus Models sem salvar no banco:

```php
class FaqTopic extends BaseModel
{
    // Computed attribute: calcula upvotes dinamicamente
    public function getUpvotesAttribute(): int
    {
        return $this->votes()
            ->where('vote_type', 'up')
            ->count();
    }
    
    // Computed attribute: calcula downvotes dinamicamente
    public function getDownvotesAttribute(): int
    {
        return $this->votes()
            ->where('vote_type', 'down')
            ->count();
    }
    
    // Computed attribute: retorna voto do usuário autenticado
    public function getUserVoteAttribute(): ?string
    {
        if (!auth()->check()) {
            return null;
        }
        
        return $this->votes()
            ->where('user_id', auth()->id())
            ->value('vote_type');
    }
    
    // Computed attribute com lógica complexa
    public function getExperienceToNextLevelAttribute(): int
    {
        $nextLevel = $this->level + 1;
        return ($nextLevel ** 2) * 50;
    }
}
```

**Acessando computed attributes:**

```php
$topic = FaqTopic::find(1);

// Acesso direto como propriedade
$upvotes = $topic->upvotes;              // 42
$downvotes = $topic->downvotes;          // 5
$userVote = $topic->user_vote;           // 'up' ou null
$xpToNext = $topic->experience_to_next_level; // 200

// Funciona automaticamente em Resources
class TopicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'upvotes' => $this->upvotes,           // Computed
            'downvotes' => $this->downvotes,       // Computed
            'user_vote' => $this->user_vote,       // Computed
        ];
    }
}
```

**Regras para Computed Attributes:**

- ✅ Use padrão `get{Nome}Attribute()` para criar computed attributes
- ✅ Perfeitos para dados que mudam frequentemente (contadores, votos)
- ✅ Ideais para dados específicos do usuário (user_vote, permissions)
- ✅ Não exigem coluna no banco de dados
- ✅ São automaticamente serializados em JSON/Arrays
- ✅ Use tipagem de retorno apropriada
- ⚠️ Cuidado com N+1 - se usar queries, considere eager loading
- ⚠️ Evite lógica pesada - será calculado toda vez que acessar
- ❌ Não use para dados que devem persistir no banco

**Alternativa para performance (caching):**

```php
// Se o cálculo for pesado, use cache
public function getStatsAttribute(): array
{
    return Cache::remember(
        "user.{$this->id}.stats",
        now()->addMinutes(5),
        fn () => [
            'questions_asked' => $this->total_topics,
            'answers_given' => $this->total_answers,
            'best_answers' => $this->total_best_answers,
        ]
    );
}
```

### Eager Loading

```php
// ✅ Correto - evita N+1
$products = Product::with(['category', 'manufacturer'])->get();

// ❌ Errado - causa N+1
$products = Product::all();
foreach ($products as $product) {
    $product->category; // Query extra!
}
```

### Cache

```php
use Illuminate\Support\Facades\Cache;

// Cache com tempo
Cache::remember('products.active', 3600, function () {
    return Product::where('active', true)->get();
});

// Cache de tag
Cache::tags(['products'])->remember('products.all', 3600, function () {
    return Product::all();
});

// Limpar cache
Cache::tags(['products'])->flush();
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
public function update(Product $product): Model
{
    // Sem validação nenhuma!
    return parent::update($product);
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

public function update(Product $product): Model
{
    // Validação manual explícita
    data($this->validate(UpdateRequest::class));
    return parent::update($product);
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

public function update(Product $product): Model
{
    // Validação automática via UpdateRequest
    // Dados já estão em data()
    return parent::update($product);
}

// ✅ CORRETO - método customizado COM entrada
// ADICIONAR em $initializedAutoDataTrait
protected $initializedAutoDataTrait = [
    'customAction', // Usa validação automática
];

public function customAction(Product $product): Model
{
    // Como está em $initializedAutoDataTrait:
    // - Crie CustomActionRequest
    // - Validação automática
    // - Dados já em data()
    
    $data = data(); // Dados validados
    return $product;
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

- **Laravel Sanctum**: Autenticação API
- **Laravel Telescope**: Debug e monitoramento (dev)
- **Pest PHP**: Framework de testes
- **Spatie/Laravel-Query-Builder**: Query builder para APIs
- **Spatie/Laravel-Permission**: Gerenciamento de roles e permissões
- **OwenIt/Auditing**: Auditoria de mudanças
- Packages customizados conforme necessidade do projeto

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

1. **Model**
```php
use App\Models\Product;
```

2. **Requests**
```php
// StoreRequest.php
// UpdateRequest.php
// IndexRequest.php (se tiver filtros complexos)
```

3. **Service**
```php
class ProductService extends BaseService
{
    protected $model = Product::class;
    
    protected array $searchableColumns = ['name', 'sku'];
    
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
    
    public function update(Product $product): Model
    {
        data($this->validate(UpdateRequest::class));
        return parent::update($product);
    }
    
    public function index(): Data
    {
        // Validação automática via IndexRequest
        return parent::index();
    }
    
    public function activate(Product $product): Model
    {
        // Sem entrada do usuário
        $product->update(['active' => true]);
        return $product;
    }
}
```

4. **Controller**
```php
class ProductController
{
    public function __construct(
        protected ProductService $service
    ) {}
}
```

5. **Routes**
```php
Route::apiResource('products', ProductController::class);
```

6. **Tests**
```php
test('lista produtos', function () {
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

## 🏢 Organização de Hierarquia em FormRequests

Quando trabalhando com recursos hierárquicos, organize FormRequests refletindo a hierarquia:

```
app/Http/Requests/Api/Private/V1/
└── Product/
    ├── StoreRequest.php
    ├── UpdateRequest.php  
    ├── Category/
    │   ├── StoreRequest.php
    │   └── UpdateRequest.php
    ├── Variant/
    │   ├── StoreRequest.php
    │   └── UpdateRequest.php
    └── Stock/
        ├── AdjustRequest.php
        └── TransferRequest.php
```

**Namespace:** `App\Http\Requests\Api\Private\V1\Product\Category`

**Benefícios:**
- Organização visual clara da hierarquia
- Facilita localização de validações
- Evita conflitos de nomenclatura
- Melhora manutenibilidade

---

## 🗂️ Models no Skeleton vs Services Locais

### Models Compartilhados (FalconERP/Skeleton)

Models compartilhados entre microserviços devem ficar no Skeleton:

```php
// No Skeleton: FalconERP/Skeleton/src/Models/Erp/Stock/
use FalconERP\Skeleton\Models\Erp\Stock\Warehouse;
use FalconERP\Skeleton\Models\Erp\Stock\WarehousePosition;
```

**IMPORTANTE:**
- ✅ Migrations ficam no microserviço que cria a tabela
- ✅ Models no Skeleton para reuso entre serviços
- ✅ Factories no microserviço (referenciam model do Skeleton)
- ✅ Policies no microserviço que possui as regras de negócio

### Models Locais

Models específicos de um microserviço ficam no próprio serviço:

```php
// No serviço: app/Models/Erp/Private/V1/
use App\Models\Erp\Private\V1\LocalModel;
```

---

## 🛡️ Policies e Gates: Substituindo abort_if/abort_unless

### O Problema com abort_if

```php
// ❌ EVITE - Lógica de negócio dispersa
public function block(WarehousePosition $position): Model
{
    abort_if(
        PositionStatusEnum::AVAILABLE !== $position->status,
        422,
        'Only available positions can be blocked'
    );
    
    // lógica de bloqueio...
}
```

**Problemas:**
- Lógica de autorização misturada com lógica de negócio
- Dificulta testes unitários
- Sem reuso de regras
- Código duplicado entre métodos

### A Solução: Policies + Gates

```php
// ✅ PREFIRA - Policy centraliza regras
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

#[UsePolicy(WarehousePositionPolicy::class)]
class WarehousePosition extends Model { }

class WarehousePositionPolicy
{
    public function block(User $user, WarehousePosition $position): Response
    {
        if (PositionStatusEnum::AVAILABLE !== $position->status) {
            return Response::deny(__('Only available positions can be blocked'));
        }
        
        if ($position->has_active_stock) {
            return Response::deny(__('Cannot block position with active stock'));
        }
        
        return Response::allow();
    }
}

// No Service
class WarehousePositionService extends BaseService
{
    public function block(WarehousePosition $position): Model
    {
        Gate::inspect('block', $position)->authorize();
        
        // Apenas lógica de negócio
        $position->update(['status' => PositionStatusEnum::BLOCKED]);
        
        return $position;
    }
}
```

**Vantagens:**
- ✅ Regras centralizadas e reutilizáveis
- ✅ Fácil de testar isoladamente
- ✅ Mensagens de erro consistentes
- ✅ Separação clara de responsabilidades
- ✅ Suporte a múltiplas validações
- ✅ `Gate::inspect()` retorna detalhes da falha

---

## 📦 Resources: Transformação de Dados

Sempre crie Resources para transformar Models em respostas JSON:

```php
namespace App\Http\Resources\Erp\Stock;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehousePositionIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // IDs sempre como integer
            'id' => (int) $this->id,
            'warehouse_aisle_id' => (int) $this->warehouse_aisle_id,
            
            // Enums: value e label
            'status' => (string) $this->status?->value,
            'status_label' => (string) $this->status?->label(),
            'side' => (string) $this->side?->value,
            'side_label' => (string) $this->side?->label(),
            
            // Strings e números
            'code' => (string) $this->code,
            'level' => (int) $this->level,
            'max_weight' => (float) $this->max_weight,
            
            // Datas em ISO8601
            'blocked_at' => $this->blocked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Propriedades computadas (conditional)
            'usage_percentage' => $this->when(
                method_exists($this->resource, 'getUsagePercentage'),
                fn () => $this->getUsagePercentage()
            ),
            
            // Relacionamentos (lazy load safe)
            'aisle' => $this->whenLoaded('aisle'),
            'warehouse' => $this->whenLoaded('aisle.warehouse'),
            'stock_positions' => $this->whenLoaded('stockPositions'),
        ];
    }
}
```

**Registre no Controller:**

```php
class WarehousePositionController extends BaseController
{
    protected string $service  = WarehousePositionService::class;
    protected string $resource = WarehousePositionIndexResource::class;
    protected array $allowedIncludes = ['aisle', 'aisle.warehouse', 'stockPositions'];
}
```

**Regras:**
- ✅ Type cast TUDO: `(int)`, `(string)`, `(float)`
- ✅ Enums: retorne `value` e `label()`
- ✅ Datas: `->toISOString()` (padrão ISO8601)
- ✅ Null safety: `?->` operator
- ✅ Computed: `$this->when()` com `method_exists()`
- ✅ Relacionamentos: `$this->whenLoaded()`

---

## 🧪 Factories com States

Use states para criar variações de models em testes:

```php
namespace Database\Factories\Erp\Stock;

use FalconERP\Skeleton\Models\Erp\Stock\WarehousePosition;
use FalconERP\Skeleton\Enums\Erp\Stock\PositionStatusEnum;

class WarehousePositionFactory extends Factory
{
    protected $model = WarehousePosition::class;

    public function definition(): array
    {
        return [
            'warehouse_aisle_id' => WarehouseAisle::factory(),
            'code' => $this->faker->unique()->bothify('POS-###-??'),
            'level' => $this->faker->numberBetween(1, 5),
            'status' => PositionStatusEnum::AVAILABLE,
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PositionStatusEnum::BLOCKED,
            'blocked_at' => now(),
        ]);
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PositionStatusEnum::OCCUPIED,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PositionStatusEnum::MAINTENANCE,
        ]);
    }
}
```

**Uso nos Testes:**

```php
// Posição disponível (padrão)
$position = WarehousePosition::factory()->create();

// Posição bloqueada
$blocked = WarehousePosition::factory()->blocked()->create();

// Múltiplas posições com estados diferentes
$positions = WarehousePosition::factory()
    ->count(3)
    ->sequence(
        ['status' => PositionStatusEnum::AVAILABLE],
        ['status' => PositionStatusEnum::BLOCKED],
        ['status' => PositionStatusEnum::OCCUPIED],
    )
    ->create();
```

---

## 🧭 Rotas: Padrão do Projeto

### Estrutura Correta

```php
// routes/Erp/Private/WarehousePositions.php
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('v1.')
    ->namespace('V1')
    ->controller('WarehousePositionController')  // ✅ STRING!
    ->group(function (): void {
        // Ações customizadas ANTES do apiResource
        Route::post('{id}/block', 'block')->name('block');
        Route::post('{id}/unblock', 'unblock')->name('unblock');
        Route::get('{id}/usage', 'usage')->name('usage');
        
        // apiResource por último
        Route::apiResource('', 'WarehousePositionController')
            ->parameters(['' => 'id']);
    });
```

### ❌ Evite

```php
// ❌ Array em controller
->controller([WarehousePositionController::class])

// ❌ Parâmetro não padronizado
->parameters(['' => 'position_id'])

// ❌ apiResource antes das rotas customizadas
Route::apiResource('', 'WarehousePositionController');
Route::post('{id}/block', 'block');  // Nunca será alcançada!
```

### ✅ Regras

1. **Controller como string**: `->controller('ControllerName')`
2. **Parâmetro sempre 'id'**: `->parameters(['' => 'id'])`
3. **apiResource por último**: Evita conflitos de rota
4. **Ações customizadas primeiro**: Declaradas antes do apiResource
5. **Namespace V1**: Sempre use `->namespace('V1')`

---

## 🎯 Migrations: Ordem e Timestamps

### Problema: Timestamps Duplicados

Quando migrations têm o mesmo timestamp, a ordem de execução é imprevisível:

```
❌ 2025_12_14_161307_create_warehouse_positions_table.php
❌ 2025_12_14_161307_create_stock_positions_table.php
```

Laravel pode executar `stock_positions` antes de `warehouse_positions`, causando erro de foreign key.

### Solução: Sufixos Numéricos

Adicione sufixo numérico ao timestamp:

```
✅ 2025_12_15_161307_000_create_products_table.php
✅ 2025_12_15_161307_100_create_product_variants_table.php
```

**Convenção:**
- Incremente de **100 em 100**: `_000`, `_100`, `_200`, `_300`
- Deixa espaço para inserir migrations intermediárias futuras
- Mantém ordem alfabética = ordem de execução

### Quando Usar

Use sufixos quando:
- ✅ Criando múltiplas migrations no mesmo minuto
- ✅ Migrations com dependências entre si (foreign keys)
- ✅ Migrations relacionadas logicamente

Não precisa quando:
- ❌ Migration isolada sem dependências
- ❌ Migrations em minutos diferentes

---

**Última atualização**: 15/12/2025  
**Versão**: 2.0.0 - Versão genérica para projetos Laravel

---

##  Organiza��o de Hierarquia em FormRequests

Quando trabalhando com recursos hier�rquicos, organize FormRequests refletindo a hierarquia:

```
app/Http/Requests/Erp/Private/V1/
 Warehouse/
     StoreRequest.php
     UpdateRequest.php  
     Aisle/
        StoreRequest.php
        UpdateRequest.php
     Position/
        StoreRequest.php
        BlockRequest.php
     StockPosition/
         AllocateRequest.php
         TransferRequest.php
```

**Namespace:** `App\Http\Requests\Erp\Private\V1\Warehouse\Aisle`

---

##  Organização de Models

### Models por Contexto

Organize models por domínio/contexto:
- `use App\Models\Product`
- `use App\Models\Order`
- `use App\Models\Customer`

**IMPORTANTE:**
-  Migrations no mesmo projeto
-  Models organizados por contexto
-  Factories junto aos models

---

##  Policies e Gates

Substitua `abort_if`/`abort_unless` por Policies:

```php
//  EVITE
abort_if(condition, 422, 'message');

//  PREFIRA
Gate::inspect('action', $model)->authorize();
```

**Policy:**
```php
#[UsePolicy(ProductPolicy::class)]
class Product extends Model { }

class ProductPolicy
{
    public function update(User $user, Product $product): Response
    {
        if ($product->user_id !== $user->id) {
            return Response::deny(__('You can only edit your own products'));
        }
        return Response::allow();
    }
}
```

---

##  Resources

```php
class ProductIndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'sku' => (string) $this->sku,
            'price' => (float) $this->price,
            'active' => (bool) $this->active,
            'created_at' => $this->created_at,
            'discount_percentage' => $this->when(
                method_exists($this->resource, 'getDiscount'),
                fn () => $this->getDiscount()
            ),
            'category' => $this->whenLoaded('category'),
        ];
    }
}
```

Registre: `protected string $resource = ProductIndexResource::class;`

---

##  Factories com States

```php
public function inactive(): static
{
    return $this->state(fn (array $attributes): array => [
        'active' => false,
    ]);
}
```

Uso: `Product::factory()->inactive()->create();`

---

##  Rotas

```php
Route::prefix('v1')
    ->name('v1.')
    ->namespace('V1')
    ->controller('ProductController')  // String!
    ->group(function (): void {
        Route::post('{id}/activate', 'activate')->name('activate');
        Route::apiResource('', 'ProductController')->parameters(['' => 'id']);
    });
```

**Regras:**
-  String em controller, n�o array
-  Par�metro sempre 'id'
-  apiResource por �ltimo

---

##  Migrations com Timestamps Duplicados

**Problema:** Mesmo timestamp causa erro de ordem

**Solu��o:** Adicione sufixo num�rico

```
 2025_12_14_161307_000_create_warehouse_positions_table.php
 2025_12_14_161307_100_create_stock_positions_table.php
```

Incremente de 100 em 100.

---

**Última atualização**: 15/12/2025
**Versão**: 2.0.0 - Versão genérica para projetos Laravel
