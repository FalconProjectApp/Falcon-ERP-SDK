# Guia de Migração - Comandos Tenant para Skeleton

## O que mudou?

Os comandos tenant agora estão no **Falcon-ERP-SDK** (Skeleton) e são automaticamente disponibilizados para todos os microserviços que importam o pacote.

## Novos comandos disponíveis

| Comando Antigo | Novo Comando | Descrição |
|---------------|--------------|-----------|
| `php artisan tenant` | `php artisan tenant:migrate` | Executar migrations |
| `php artisan tenant:rollback` | `php artisan tenant:rollback` | Reverter migrations |
| `php artisan tenant:status` | `php artisan tenant:status` | Ver status das migrations |
| `php artisan check:migrations` | `php artisan tenant:check` | Verificar migrations de um tenant |

## Passos de migração por microserviço

### 1. Remover comandos e jobs locais

Se o microserviço possui os comandos/jobs localmente, remova:

```bash
# Remover comandos
rm app/Console/Commands/tenant.php
rm app/Console/Commands/TenantRollback.php
rm app/Console/Commands/TenantStatus.php
rm app/Console/Commands/CheckMigrations.php

# Remover jobs
rm app/Jobs/TenantMigrationJob.php
rm app/Jobs/TenantRollbackJob.php
```

### 2. Atualizar scripts/automações

Se você tem scripts que usam os comandos antigos, atualize:

```bash
# Antes
php artisan tenant

# Depois
php artisan tenant:migrate
```

### 3. Verificar funcionamento

```bash
# Listar comandos disponíveis
php artisan list | grep tenant

# Deve mostrar:
# tenant:migrate
# tenant:rollback
# tenant:status
# tenant:check
```

### 4. Testar em um tenant

```bash
# Status de um tenant específico
php artisan tenant:status nome_do_tenant

# Executar migration
php artisan tenant:migrate nome_do_tenant
```

## Microserviços que precisam ser migrados

- [x] **Falcon-Finance-Service** - Já está usando o Skeleton
- [ ] **Falcon-Stock-Service** - Precisa remover comandos locais
- [ ] **Falcon-BigData-Service** - Verificar se tem comandos locais
- [ ] **Falcon-Fiscal-Service** - Verificar se tem comandos locais
- [ ] **Falcon-People-Service** - Verificar se tem comandos locais
- [ ] **Falcon-Auth-Service** - Verificar se tem comandos locais
- [ ] **Falcon-Shop-Service** - Verificar se tem comandos locais
- [ ] **Falcon-Order-Service** - Verificar se tem comandos locais

## Vantagens da centralização

1. **Manutenção única**: Correções e melhorias em um único lugar
2. **Consistência**: Todos os microserviços usam a mesma versão
3. **Atualizações automáticas**: Ao atualizar o Skeleton, todos os serviços recebem
4. **Menos código duplicado**: Reduz complexidade dos microserviços

## Namespace dos Jobs

Se você precisa despachar os jobs manualmente:

```php
// Antes (job local)
use App\Jobs\TenantMigrationJob;

// Depois (job do Skeleton)
use FalconERP\Skeleton\Jobs\TenantMigrationJob;
```

## Troubleshooting

### Comandos não aparecem
1. Verificar se o SkeletonProvider está registrado
2. Limpar cache: `php artisan config:clear`
3. Verificar versão do Skeleton no composer.json

### Erro ao executar comando
1. Verificar configuração da conexão 'tenant' em database.php
2. Verificar se os models do BackOffice estão acessíveis
3. Verificar logs em storage/logs/laravel.log

### Jobs não executam
1. Verificar se a queue está configurada
2. Verificar se o worker está rodando: `php artisan queue:work`
3. Verificar tabela job_batches existe: `php artisan queue:batches-table`

## Suporte

Para dúvidas ou problemas, consulte:
- [Documentação completa](./TENANT_COMMANDS.md)
- Logs do Laravel em `storage/logs/`
- Verificar status do batch no banco: tabela `job_batches`
