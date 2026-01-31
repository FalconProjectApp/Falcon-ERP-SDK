# Comandos Tenant - Falcon ERP SDK

Este pacote fornece comandos para gerenciar migrations em múltiplos tenants de forma eficiente.

## Comandos Disponíveis

### 1. tenant:migrate
Executa migrations em um ou mais bancos de dados tenant.

**Uso:**
```bash
# Executar em um tenant específico
php artisan tenant:migrate nome_do_tenant

# Executar em todos os tenants de um grupo (com confirmação)
php artisan tenant:migrate

# Executar em todos os tenants de todos os grupos (sem confirmação)
php artisan tenant:migrate --all
```

**Características:**
- Execução síncrona para 1 tenant
- Execução assíncrona com batch jobs para múltiplos tenants
- Rollback automático em caso de falha
- Progress bar com status em tempo real

### 2. tenant:rollback
Reverte migrations em um ou mais bancos de dados tenant.

**Uso:**
```bash
# Rollback em um tenant específico
php artisan tenant:rollback nome_do_tenant

# Rollback em um tenant específico com número de steps
php artisan tenant:rollback nome_do_tenant --step=1

# Rollback em todos os tenants
php artisan tenant:rollback --all

# Rollback em todos os tenants com step
php artisan tenant:rollback --all --step=2
```

**Opções:**
- `--step=N`: Número de migrations a reverter

### 3. tenant:status
Verifica o status das migrations em um ou mais tenants.

**Uso:**
```bash
# Status de um tenant específico
php artisan tenant:status nome_do_tenant

# Status de todos os tenants
php artisan tenant:status --all

# Mostrar apenas tenants com migrations pendentes
php artisan tenant:status --all --only=pending
```

**Opções:**
- `--only=pending`: Mostra apenas tenants com migrations pendentes

### 4. tenant:check
Verifica as últimas migrations executadas em um tenant específico.

**Uso:**
```bash
php artisan tenant:check nome_do_tenant
```

## Jobs

### TenantMigrationJob
Job responsável por executar migrations em um tenant específico.

**Características:**
- Timeout: 300 segundos
- Tentativas: 1
- Suporta batch processing
- Logs detalhados de execução

### TenantRollbackJob
Job responsável por executar rollback em um tenant específico.

**Características:**
- Timeout: 300 segundos
- Tentativas: 1
- Suporta batch processing
- Suporta rollback com steps
- Logs detalhados de execução

## Configuração

### Requisitos
- Laravel Queue configurado (para batch jobs)
- Conexão 'tenant' configurada no `config/database.php`
- Models do BackOffice (Database, DatabaseGroup) configurados

### Exemplo de Configuração
```php
// config/database.php
'tenant' => [
    'driver' => 'pgsql',
    'host' => env('TENANT_DB_HOST', '127.0.0.1'),
    'port' => env('TENANT_DB_PORT', '5432'),
    'database' => null, // Será configurado dinamicamente
    'username' => null, // Será configurado dinamicamente
    'password' => null, // Será configurado dinamicamente
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
],
```

## Fluxo de Trabalho

### Para Migrations
1. O comando identifica o(s) tenant(s) alvo
2. Para 1 tenant: execução síncrona
3. Para múltiplos tenants:
   - Cria batch de jobs
   - Monitora progresso em tempo real
   - Em caso de falha: cancela batch e executa rollback automático

### Para Rollback
1. O comando identifica o(s) tenant(s) alvo
2. Executa rollback síncrono (1 tenant) ou assíncrono (múltiplos)
3. Logs detalhados de cada operação

## Logs
Todos os comandos geram logs detalhados em:
- Canal padrão do Laravel
- Informações incluem: database, group, output, erros

## Exemplos de Uso

### Cenário 1: Deploy em produção
```bash
# Verificar status antes
php artisan tenant:status --all --only=pending

# Executar migrations em todos os tenants
php artisan tenant:migrate --all

# Verificar se teve sucesso
php artisan tenant:status --all --only=pending
```

### Cenário 2: Reverter última migration
```bash
# Reverter última migration em todos os tenants
php artisan tenant:rollback --all --step=1
```

### Cenário 3: Debug de um tenant específico
```bash
# Ver status
php artisan tenant:status meu_tenant

# Ver últimas migrations
php artisan tenant:check meu_tenant

# Executar migration
php artisan tenant:migrate meu_tenant
```

## Tratamento de Erros

- **Falha em migration**: Batch é cancelado e rollback automático é executado nos tenants já migrados
- **Falha em rollback**: Batch é cancelado, logs são gerados
- **Timeout**: Job falha após 300 segundos
- **Tenant não encontrado**: Erro é exibido com lista de tenants disponíveis

## Monitoramento

### Progress Bar
Mostra em tempo real:
- Progresso atual (X/Y)
- Percentual completado
- Mensagem de status

### Batch
- ID do batch gerado
- Status de cada job
- Total de jobs processados
- Total de falhas
