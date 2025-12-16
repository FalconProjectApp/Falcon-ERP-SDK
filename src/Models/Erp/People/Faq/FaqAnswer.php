<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People\Faq;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\EventDispatcherObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Auth\Access\Attributes\UsePolicy;

#[ObservedBy([
    CacheObserver::class,
    EventDispatcherObserver::class,
])]
#[UsePolicy('App\\Policies\\FaqAnswerPolicy')]
class FaqAnswer extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $connection = 'pgsql';

    protected $table = 'faq_answers';

    protected $fillable = [
        'topic_id',
        'user_id',
        'parent_id',
        'content',
        'votes_count',
        'is_best_answer',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'votes_count'    => 'integer',
        'is_best_answer' => 'boolean',
        'is_edited'      => 'boolean',
        'edited_at'      => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    protected $appends = [
        'actions',
    ];

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(FaqTopic::class, 'topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FaqAnswer::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FaqAnswer::class, 'parent_id');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(FaqVote::class, 'votable');
    }

    // Scopes

    public function scopeBestAnswers($query)
    {
        return $query->where('is_best_answer', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // ActionTrait Implementation

    protected function setActions(): array
    {
        return [
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    protected function canView(): bool
    {
        return ! $this->trashed();
    }

    protected function canRestore(): bool
    {
        return $this->trashed();
    }

    protected function canUpdate(): bool
    {
        $currentUserId = auth()->id();

        return ! $this->trashed() && ($this->user_id === $currentUserId || auth()->user()?->hasRole('admin'));
    }

    protected function canDelete(): bool
    {
        $currentUserId = auth()->id();

        return ! $this->trashed() && ($this->user_id === $currentUserId || auth()->user()?->hasRole('admin'));
    }

    protected function canFollow(): bool
    {
        return false;
    }

    protected function canUnfollow(): bool
    {
        return false;
    }

    // Helper Methods

    public function markAsBest(): void
    {
        $this->update(['is_best_answer' => true]);
        $this->topic->markBestAnswer($this->id);
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    // Computed attributes

    public function getUpvotesAttribute(): int
    {
        return $this->votes()->where('vote_type', 'up')->count();
    }

    public function getDownvotesAttribute(): int
    {
        return $this->votes()->where('vote_type', 'down')->count();
    }

    public function getUserVoteAttribute(): ?string
    {
        if (! auth()->check()) {
            return null;
        }

        $vote = $this->votes()->where('user_id', auth()->id())->first();

        return $vote?->vote_type;
    }
}
