<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Console\Commands;

use FalconERP\Skeleton\Jobs\TenantRollbackJob;
use Exception;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\DataBase\DatabaseGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class TenantRollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:rollback
        {tenant? : Specific tenant database name}
        {--step= : The number of migrations to be reverted}
        {--all : Run on all tenants without confirmation}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback migrations for multiple tenant databases';

    protected $groupSelected;
    protected array $databases = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {

            $this->info('Schema: ' . $this->getSchema());

            $this->groups();

            if (!$this->option('all') && !$this->confirm('Deseja continuar com o rollback?', false)) {
                $this->error('Operação cancelada pelo usuário.');

                return;
            }

            $step = $this->option('step') ? (int) $this->option('step') : null;

            // Se for apenas 1 tenant, executar de forma síncrona
            if (count($this->databases) === 1) {
                $this->info('Executando rollback de forma síncrona...');
                $database = $this->databases[0];

                $this->info('Rollback no banco de dados: ' . $database->base);

                $database->connect();

                try {
                    $options = [
                        '--database' => 'tenant',
                        '--force' => true,
                    ];

                    if ($step !== null) {
                        $options['--step'] = $step;
                    }

                    $this->call('migrate:rollback', $options);

                    $this->newLine();
                    $this->info('Rollback concluído com sucesso!');
                } catch (Exception $e) {
                    $this->error('Erro ao executar rollback: ' . $e->getMessage());
                    throw $e;
                } finally {
                    $database->disconnect();
                }

                return;
            }

            // Múltiplos tenants - usar batch de jobs
            $this->info('Iniciando o rollback com batch de jobs...');
            $this->info('Total de tenants: ' . count($this->databases));

            // Criar jobs para cada tenant
            $jobs = collect($this->databases)->map(function (Database $database) use ($step) {
                return new TenantRollbackJob($database->id, $step);
            })->toArray();

            // Criar batch
            $batch = Bus::batch($jobs)
                ->name('Tenant Rollback - ' . now()->format('Y-m-d H:i:s'))
                ->allowFailures(false)
                ->then(function (\Illuminate\Bus\Batch $batch) {
                    \Illuminate\Support\Facades\Log::info('Todos os rollbacks foram concluídos com sucesso!');
                })
                ->catch(function (\Illuminate\Bus\Batch $batch, \Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Erro durante os rollbacks.', [
                        'error' => $e->getMessage(),
                    ]);
                })
                ->finally(function (\Illuminate\Bus\Batch $batch) {
                    if ($batch->cancelled() || $batch->hasFailures()) {
                        \Illuminate\Support\Facades\Log::warning('Batch de rollback cancelado ou com falhas.');
                    }
                })
                ->dispatch();

            $this->info('Batch criado com ID: ' . $batch->id);
            $this->info('Processando rollbacks de forma assíncrona...');
            $this->newLine();

            // Monitorar progresso do batch
            $bar = $this->output->createProgressBar(count($this->databases));
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
            $bar->setMessage('Processando...');
            $bar->start();

            $lastProcessed = 0;
            while (!$batch->finished()) {
                sleep(1);
                $batch = $batch->fresh();

                $processed = $batch->processedJobs();
                if ($processed > $lastProcessed) {
                    $bar->advance($processed - $lastProcessed);
                    $lastProcessed = $processed;
                }

                if ($batch->hasFailures()) {
                    $bar->setMessage('Falha detectada! Cancelando batch...');
                    $batch->cancel();
                    break;
                }
            }

            $bar->finish();
            $this->newLine(2);

            if ($batch->hasFailures()) {
                $this->error('Alguns rollbacks falharam.');
                $this->error('Total de falhas: ' . $batch->failedJobs);

                return;
            }

            $this->info('Total de bancos de dados processados: ' . count($this->databases));
            $this->info('Operação concluída com sucesso!');
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());

            throw new Exception('Error in tenant:rollback command: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getSchema(): string
    {
        return config('database.connections.tenant.search_path', 'public');
    }

    private function groups(): void
    {
        $groupList = DatabaseGroup::get();

        if (empty($groupList)) {
            $this->error(__('Nenhum grupo de banco de dados encontrado.'));

            return;
        }

        // Se --all, processar todos os grupos
        if ($this->option('all')) {
            $this->databases = [];

            foreach ($groupList as $group) {
                $databases = $group->databases()->with('group')->get();

                $this->databases = array_merge($this->databases, $databases->all());
            }

            $this->info('Executando em TODOS os grupos e bancos de dados');
            $this->info('Total de bancos de dados: ' . count($this->databases));

            return;
        }

        $this->groupSelected = $this->choice(
            __('Em qual servidor?'),
            $groupList->pluck('description')->toArray(),
        );

        $this->groupSelected = $groupList->firstWhere('description', $this->groupSelected);

        Config::set([
            'database.connections.tenant.host'           => app()->isLocal() ? config('database.connections.pgsql.host') : $this->groupSelected->host,
            'database.connections.tenant.port'           => app()->isLocal() ? config('database.connections.pgsql.port') : $this->groupSelected->port,
            'database.connections.tenant.username'       => $this->groupSelected->user,
            'database.connections.tenant.password'       => Crypt::decryptString($this->groupSelected->secret),
            'database.connections.tenant.charset'        => config('database.connections.pgsql.charset'),
            'database.connections.tenant.prefix_indexes' => config('database.connections.pgsql.prefix_indexes'),
            'database.connections.tenant.sslmode'        => config('database.connections.pgsql.sslmode'),
        ]);

        if (empty($this->groupSelected)) {
            $this->error(__('Grupo de banco de dados não encontrado.'));

            return;
        }

        $databases = $this->groupSelected
            ->databases()
            ->with('group')
            ->get();

        // Filtrar por tenant específico se fornecido
        if ($specificTenant = $this->argument('tenant')) {
            $database = $databases->firstWhere('base', $specificTenant);

            if ($database) {
                $this->databases = [$database];
                $this->info('Executando apenas no tenant: ' . $specificTenant);
            } else {
                $this->error('Tenant "' . $specificTenant . '" não encontrado no grupo selecionado.');
                $this->info('Tenants disponíveis: ' . $databases->pluck('base')->implode(', '));
                $this->databases = [];
                return;
            }
        } else {
            $this->databases = $databases->all();
        }

        $this->info('Grupo de banco de dados selecionado com sucesso: ' . $this->groupSelected->description);
        $this->info('ID do grupo: ' . $this->groupSelected->id);
        $this->info('Conexão: ' . $this->groupSelected->host . ':' . $this->groupSelected->port);
        $this->info('Total de bancos de dados: ' . count($this->databases));
        $this->info('Banco de dados: ' . collect($this->databases)->pluck('base')->implode(', '));
    }
}
