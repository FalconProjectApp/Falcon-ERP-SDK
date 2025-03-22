<?php

namespace App\Models\BackOffice;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;

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
    public function scopeByIpAndAgent(Builder $query, string $ip, string $agent): Builder
    {
        return $query
            ->where('ip', $ip)
            ->where('agent', $agent);
    }
}
