<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
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

#[ObservedBy([
    CacheObserver::class,
    EventDispatcherObserver::class,
])]
#[UsePolicy('App\\Policies\\FaqTopicPolicy')]
class FaqTopic extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use SoftDeletes;

    protected $connection = 'pgsql';

    protected $table = 'faq_topics';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'status',
        'views_count',
        'answers_count',
        'votes_count',
        'best_answer_id',
        'is_pinned',
        'is_solved',
        'is_locked',
        'last_activity_at',
        'tags',
    ];

    protected $casts = [
        'views_count'      => 'integer',
        'answers_count'    => 'integer',
        'votes_count'      => 'integer',
        'is_pinned'        => 'boolean',
        'is_solved'        => 'boolean',
        'is_locked'        => 'boolean',
        'tags'             => 'array',
        'last_activity_at' => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    protected $appends = [
        'actions',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'category_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FaqAnswer::class, 'topic_id');
    }

    public function bestAnswer(): BelongsTo
    {
        return $this->belongsTo(FaqAnswer::class, 'best_answer_id');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(FaqVote::class, 'votable');
    }

    // Scopes

    #[Scope]
    public function open(Builder $query)
    {
        return $query->where('status', 'open');
    }

    #[Scope]
    public function pinned(Builder $query)
    {
        return $query->where('is_pinned', true);
    }

    #[Scope]
    public function popular(Builder $query)
    {
        return $query->where('views_count', '>', 100)->orderBy('views_count', 'desc');
    }

    #[Scope]
    public function recent(Builder $query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper Methods

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function markBestAnswer(string $answerId): void
    {
        $this->update([
            'best_answer_id'   => $answerId,
            'status'           => 'answered',
            'is_solved'        => true,
            'last_activity_at' => now(),
        ]);
    }

    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
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
        if (!auth()->check()) {
            return null;
        }

        $vote = $this->votes()->where('user_id', auth()->id())->first();

        return $vote?->vote_type;
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
        return !$this->trashed();
    }

    protected function canRestore(): bool
    {
        return $this->trashed();
    }

    protected function canUpdate(): bool
    {
        $currentUserId = auth()->id();

        return !$this->trashed() && ($this->user_id === $currentUserId || auth()->user()?->hasRole('admin'));
    }

    protected function canDelete(): bool
    {
        $currentUserId = auth()->id();

        return !$this->trashed() && ($this->user_id === $currentUserId || auth()->user()?->hasRole('admin'));
    }

    protected function canFollow(): bool
    {
        return !$this->trashed();
    }

    protected function canUnfollow(): bool
    {
        return !$this->trashed();
    }
}
