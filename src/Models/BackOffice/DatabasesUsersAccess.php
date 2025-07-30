<?php

namespace FalconERP\Skeleton\Models\BackOffice;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

class DatabasesUsersAccess extends BaseModel
{
    use HasFactory;

    protected $connection = 'pgsql';
    public $timestamps    = false;

    protected $fillable = [
        'database_id',
        'base_people_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
            'bc_'.request()->database->base
        );

        $this->setConnection('tenant');

        $people = $this->belongsTo(People::class, 'base_people_id');

        $this->setConnection('pgsql');

        return $people;
    }
}
