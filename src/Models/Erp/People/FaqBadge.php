<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class FaqBadge extends BaseModel
{
    protected $connection = 'pgsql';

    protected $table = 'faq_badges';

    protected $fillable = [
        'type',
        'name',
        'description',
        'icon',
        'color',
        'rarity',
        'required_points',
        'is_active',
    ];

    protected $casts = [
        'required_points' => 'integer',
        'is_active'       => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // Relationships

    public function users(): HasMany
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderByPoints($query)
    {
        return $query->orderBy('required_points', 'asc');
    }
}
