<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [];

    public $allowedIncludes = [
        'state',
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'states_id');
    }

    public function scopeByState(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'state_id'), function ($query, $params) {
                $query->whereIn('states_id', $params);
            });
    }
}
