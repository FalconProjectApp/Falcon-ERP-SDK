<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Console\Commands;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckMigrations extends Command
{
    protected $signature = 'tenant:check {tenant}';
    protected $description = 'Check migrations in tenant database';

    public function handle(): void
    {
        $tenantName = $this->argument('tenant');

        $database = Database::where('base', $tenantName)->with('group')->first();

        if (!$database) {
            $this->error("Tenant {$tenantName} not found");
            return;
        }

        $database->connect();

        $this->info("Checking migrations for tenant: {$database->base}");
        $this->info("Database: " . config('database.connections.tenant.database'));
        $this->info("Search path: " . config('database.connections.tenant.search_path'));

        // Verificar search_path atual
        $searchPath = DB::connection('tenant')->select('SHOW search_path')[0]->search_path ?? 'unknown';
        $this->info("Current search_path: {$searchPath}");

        // Listar últimas migrations
        $migrations = DB::connection('tenant')->table('migrations')->orderBy('id', 'desc')->limit(5)->get();

        $this->table(['ID', 'Migration', 'Batch'], $migrations->map(fn($m) => [
            $m->id,
            $m->migration,
            $m->batch
        ])->toArray());

        $database->disconnect();
    }
}
