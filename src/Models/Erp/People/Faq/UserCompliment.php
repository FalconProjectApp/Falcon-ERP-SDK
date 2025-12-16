<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People\Faq;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class UserCompliment extends BaseModel
{
    use SetSchemaTrait;

    protected $connection = 'pgsql';

    protected $table = 'user_compliments';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'answer_id',
        'compliment_type',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(FaqAnswer::class, 'answer_id');
    }

    // Scopes

    public function scopeForUser($query, string $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('compliment_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
