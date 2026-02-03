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
#[UsePolicy('App\\Policies\\FaqAnswerPolicy')]
class FaqAnswer extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
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
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(FaqVote::class, 'votable');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    #[Scope]
    public function byBestAnswers(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'is_best_answer'), function ($query, $params) {
                $query->where('is_best_answer', $params);
            });
    }

    #[Scope]
    public function byTopLevel(Builder $query, string | array $params = []): Builder
    {
        return $query->whereNull('parent_id');
    }

    #[Scope]
    public function byParentId(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'parent_id'), function ($query, $params) {
                if ('null' === $params || null === $params) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $params);
                }
            });
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
        if (!auth()->check()) {
            return null;
        }

        $vote = $this->votes()->where('user_id', auth()->id())->first();

        return $vote?->vote_type;
    }

    /*
    |--------------------------------------------------------------------------
    | ActionTrait Implementation
    |--------------------------------------------------------------------------
    */

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
        return false;
    }

    protected function canUnfollow(): bool
    {
        return false;
    }
}
