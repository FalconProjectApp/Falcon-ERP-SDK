<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Console\Commands;

use Exception;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\DataBase\DatabaseGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\Console\Helper\ProgressBar;

class TenantStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:status
        {tenant? : Specific tenant database name}
        {--all : Run on all tenants without confirmation}
        {--only=pending : Show only tenants with pending migrations}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of migrations for multiple tenant databases';

    protected $groupSelected;
    protected array $databases = [];
    protected array $tenantsWithPending = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {

            $this->info('Schema: ' . $this->getSchema());

            $this->groups();

            if (!$this->option('all') && !$this->confirm('Deseja continuar?', false)) {
                $this->error('Operação cancelada pelo usuário.');

                return;
            }

            $this->info('Iniciando a conexão com o banco de dados...');
            $this->info('Conexão estabelecida com sucesso!');

            $bar = $this->initBar(
                'Verificando status dos bancos de dados...',
                ' %current%/%max% [%bar%] %percent:3s%% | Tempo restante: %estimated:-6s% | %message%',
                count($this->databases)
            );

            $bar->start();

            $onlyPending = $this->option('only') === 'pending';

            collect($this->databases)
                ->each(function (Database $database) use ($bar, $onlyPending): void {
                    $bar->setMessage('Verificando banco de dados: ' . $database->base . ' (Grupo: ' . $database->group->description . ')');

                    // Conectar ao banco de dados do tenant
                    $database->connect();

                    try {
                        // Verificar se há migrations pendentes
                        $migrator = app('migrator');
                        $repository = $migrator->getRepository();

                        // Definir a conexão do repository
                        $repository->setSource('tenant');

                        // Garantir que a tabela de migrations existe
                        if (!$repository->repositoryExists()) {
                            $bar->advance();
                            $bar->setMessage('');
                            $bar->display();
                            return;
                        }

                        $ran = $repository->getRan();
                        $migrationFiles = $migrator->getMigrationFiles(database_path('migrations'));
                        $pending = array_diff(array_keys($migrationFiles), $ran);

                        $hasPending = !empty($pending);
                        $pendingCount = count($pending);

                        // Se --only=pending e não tem pending, pular
                        if ($onlyPending && !$hasPending) {
                            $bar->advance();
                            $bar->setMessage('');
                            $bar->display();
                            return;
                        }

                        // Se tem pending, adicionar à lista
                        if ($hasPending) {
                            $this->tenantsWithPending[] = [
                                'database' => $database->base,
                                'group' => $database->group->description,
                                'pending_count' => $pendingCount
                            ];
                        }

                        $this->newLine();
                        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
                        $this->info('Database: bc_' . $database->base . ' (Grupo: ' . $database->group->description . ')');
                        if ($hasPending) {
                            $this->warn('Migrations pendentes: ' . $pendingCount);
                        }
                        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');

                        if ($onlyPending && $hasPending) {
                            // Mostrar apenas as migrations pendentes
                            $this->table(
                                ['Migration', 'Status'],
                                collect($pending)->map(fn($migration) => [
                                    $migration,
                                    '<fg=yellow>Pending</>'
                                ])->toArray()
                            );
                        } elseif (!$onlyPending) {
                            // Mostrar status completo apenas se não for --only=pending
                            ob_start();
                            \Illuminate\Support\Facades\Artisan::call('migrate:status', [
                                '--database' => 'tenant',
                            ]);
                            $output = ob_get_clean();
                            $this->line($output);
                        }
                    } catch (Exception $e) {
                        $this->error('An error occurred while checking status for database: ' . $database->base);
                        $this->error('Error message: ' . $e->getMessage());
                        $bar->advance();

                        return;
                    } finally {
                        // Desconectar do banco de dados do tenant
                        $database->disconnect();
                    }

                    $bar->advance();
                    $bar->setMessage('');
                    $bar->display();
                });

            $bar->finish();

            $this->newLine(2);
            $this->info('Total de bancos de dados verificados: ' . count($this->databases));

            if (!empty($this->tenantsWithPending)) {
                $this->newLine();
                $this->warn('Tenants com migrations pendentes: ' . count($this->tenantsWithPending));
                $this->table(
                    ['Database', 'Grupo', 'Migrations Pendentes'],
                    collect($this->tenantsWithPending)->map(fn($item) => [
                        $item['database'],
                        $item['group'],
                        $item['pending_count']
                    ])->toArray()
                );
            } else {
                $this->info('Nenhum tenant com migrations pendentes.');
            }

            $this->info('Operação concluída com sucesso!');
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());

            throw new Exception('Error in tenant:status command: ' . $e->getMessage(), $e->getCode(), $e);
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

    private function initBar(string $message, ?string $template = null, int $count = 0): ProgressBar
    {
        $bar = $this->output->createProgressBar($count);
        $bar->setFormat($template);
        $bar->setMessage($message);
        $bar->setBarCharacter('<fg=green>=</>');
        $bar->setEmptyBarCharacter('<fg=red>-</>');
        $bar->setProgressCharacter('<fg=yellow>></>');

        return $bar;
    }
}
