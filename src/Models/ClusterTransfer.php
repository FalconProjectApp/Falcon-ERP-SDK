<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\DataBase\DatabaseGroup;

class ClusterTransfer extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $fillable = [
        'database_id',
        'from_group_id',
        'to_group_id',
        'status',
        'message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Relacionamento com o banco migrado
     */
    public function database()
    {
        return $this->belongsTo(Database::class);
    }

    /**
     * Grupo de origem
     */
    public function fromGroup()
    {
        return $this->belongsTo(DatabaseGroup::class, 'from_group_id');
    }

    /**
     * Grupo de destino
     */
    public function toGroup()
    {
        return $this->belongsTo(DatabaseGroup::class, 'to_group_id');
    }

    /**
     * Status da migração (pending, running, success, failed)
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }


}
