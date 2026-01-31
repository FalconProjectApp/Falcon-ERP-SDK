<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Jobs;

use Exception;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

use Throwable;

class TenantMigrationJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;
    public $tries   = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $databaseId
    ) {
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "tenant-migration-{$this->databaseId}";
    }

    /**
     * Get tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['tenant-migration', "database:{$this->databaseId}"];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            Log::channel('single')->info("Job cancelled for database ID: {$this->databaseId}");

            return;
        }

        $database = null;

        try {
            $database = Database::with('group')->find($this->databaseId);

            if (!$database) {
                throw new Exception("Database with ID {$this->databaseId} not found");
            }

            // Conectar ao tenant
            $database->connect();

            Log::info("Starting migration for tenant: {$database->base}", [
                'database'            => $database->base,
                'database_id'         => $database->id,
                'group'               => $database->group->description,
                'connection_database' => config('database.connections.tenant.database'),
                'search_path'         => config('database.connections.tenant.search_path'),
                'default_connection'  => config('database.default'),
            ]);

            // Executar migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force'    => true,
            ]);

            $output = Artisan::output();

            Log::info("Migration completed for tenant: {$database->base}", [
                'database'    => $database->base,
                'database_id' => $database->id,
                'group'       => $database->group->description,
                'output'      => $output,
            ]);

            // Pequeno delay para permitir que o Telescope grave os eventos
            usleep(1000000); // 1000ms
        } catch (Exception $e) {
            Log::error("Migration failed for database ID: {$this->databaseId}", [
                'database_id' => $this->databaseId,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        error(sprintf('Ocorreu um problema no tenant_id: %d, error_message: %s',
            tenant()?->id,
            $exception->getMessage()
        ));
    }
}
