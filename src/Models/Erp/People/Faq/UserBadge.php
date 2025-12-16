<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People\Faq;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class UserBadge extends BaseModel
{
    use SetSchemaTrait;

    protected $connection = 'pgsql';

    protected $table = 'user_badges';

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
    ];

    protected $casts = [
        'earned_at'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(FaqBadge::class, 'badge_id');
    }

    // Scopes

    public function scopeRecent($query)
    {
        return $query->orderBy('earned_at', 'desc');
    }
}
