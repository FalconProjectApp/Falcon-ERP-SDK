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

class TenantRollbackJob implements ShouldQueue
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
        public int $databaseId,
        public ?int $step = null
    ) {
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "tenant-rollback-{$this->databaseId}-{$this->step}";
    }

    /**
     * Get tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['tenant-rollback', "database:{$this->databaseId}"];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            Log::info("Job cancelled for database ID: {$this->databaseId}");

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

            // Executar rollback
            $options = [
                '--database' => 'tenant',
                '--force'    => true,
            ];

            if (null !== $this->step) {
                $options['--step'] = $this->step;
            }

            Artisan::call('migrate:rollback', $options);

            $output = Artisan::output();

            Log::info("Rollback completed for tenant: {$database->base}", [
                'database'    => $database->base,
                'database_id' => $database->id,
                'group'       => $database->group->description,
                'step'        => $this->step,
                'output'      => $output,
            ]);

            usleep(1000000); // 1000ms
        } catch (Exception $e) {
            Log::error("Rollback failed for database ID: {$this->databaseId}", [
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
