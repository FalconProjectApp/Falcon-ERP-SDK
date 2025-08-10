<?php

namespace FalconERP\Skeleton\Models\BackOffice;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class UserSession extends BaseModel
{
    use Notifiable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'ip',
        'agent',
    ];

    /**
     * byDescription function.
     */
    #[Scope]
    public function byIpAndAgent(Builder $query, string $ip, string $agent): Builder
    {
        return $query
            ->where('ip', $ip)
            ->where('agent', $agent);
    }
}
