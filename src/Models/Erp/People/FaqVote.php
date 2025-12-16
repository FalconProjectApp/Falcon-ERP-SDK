<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class FaqVote extends BaseModel
{
    use SetSchemaTrait;

    protected $connection = 'pgsql';

    protected $table = 'faq_votes';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'votable_id',
        'votable_type',
        'vote_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeUpvotes($query)
    {
        return $query->where('vote_type', 'upvote');
    }

    public function scopeDownvotes($query)
    {
        return $query->where('vote_type', 'downvote');
    }
}
