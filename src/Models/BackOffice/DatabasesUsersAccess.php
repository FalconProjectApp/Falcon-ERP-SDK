<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\BackOffice;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class DatabasesUsersAccess extends BaseModel
{
    use HasFactory;
    public $timestamps = false;

    protected $connection = 'pgsql';

    protected $fillable = [
        'database_id',
        'base_people_id',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function database(): BelongsTo
    {
        return $this->belongsTo(Database::class);
    }

    /**
     * People function.
     */
    public function people(): BelongsTo
    {
        Config::set(
            'database.connections.tenant.database',
            'bc_' . request()->database->base
        );

        $this->setConnection('tenant');

        $people = $this->belongsTo(People::class, 'base_people_id');

        $this->setConnection('pgsql');

        return $people;
    }
}
