<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People\Faq;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class UserLevel extends BaseModel
{
    use SetSchemaTrait;

    protected $connection = 'pgsql';

    protected $table = 'user_levels';

    protected $fillable = [
        'user_id',
        'level',
        'title',
        'experience_points',
        'total_topics',
        'total_answers',
        'total_upvotes',
        'total_downvotes',
        'total_best_answers',
        'total_compliments',
    ];

    protected $casts = [
        'level'                => 'integer',
        'experience_points'    => 'integer',
        'total_topics'         => 'integer',
        'total_answers'        => 'integer',
        'total_upvotes'        => 'integer',
        'total_downvotes'      => 'integer',
        'total_best_answers'   => 'integer',
        'total_compliments'    => 'integer',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods

    public function addExperience(int $points): void
    {
        $this->increment('experience_points', $points);
        $this->updateLevel();
    }

    public function updateLevel(): void
    {
        // Fórmula: experience_to_next_level = (level^2) * 50
        $newLevel = 1;
        $pointsNeeded = 0;

        // Calcular nível baseado na experiência acumulada
        while ($pointsNeeded <= $this->experience_points) {
            $newLevel++;
            $pointsNeeded += ($newLevel * $newLevel) * 50;
        }

        $newLevel--; // Voltar um nível pois ultrapassou

        if ($newLevel !== $this->level) {
            $this->update([
                'level' => $newLevel,
                'title' => $this->getLevelTitle($newLevel),
            ]);
        }
    }

    public function getLevelTitle(int $level): string
    {
        return match (true) {
            $level >= 20 => 'Guru',
            $level >= 15 => 'Mestre',
            $level >= 10 => 'Expert',
            $level >= 7  => 'Avançado',
            $level >= 5  => 'Intermediário',
            $level >= 3  => 'Iniciante',
            default      => 'Novato',
        };
    }

    public function getExperienceToNextLevelAttribute(): int
    {
        $nextLevel = $this->level + 1;

        return ($nextLevel * $nextLevel) * 50;
    }

    public function getStatsAttribute(): array
    {
        return [
            'questions_asked'    => $this->total_topics,
            'answers_given'      => $this->total_answers,
            'best_answers'       => $this->total_best_answers,
            'upvotes_received'   => $this->total_upvotes,
            'helpful_votes'      => $this->total_upvotes,
        ];
    }

    public function incrementTopics(): void
    {
        $this->increment('total_topics');
    }

    public function incrementAnswers(): void
    {
        $this->increment('total_answers');
    }

    public function incrementUpvotes(): void
    {
        $this->increment('total_upvotes');
    }

    public function incrementDownvotes(): void
    {
        $this->increment('total_downvotes');
    }

    public function incrementBestAnswers(): void
    {
        $this->increment('total_best_answers');
    }

    public function incrementCompliments(): void
    {
        $this->increment('total_compliments');
    }

    // Scopes

    public function scopeTopUsers($query, int $limit = 10)
    {
        return $query->orderBy('experience_points', 'desc')->limit($limit);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
}
