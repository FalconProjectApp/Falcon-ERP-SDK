<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\Archive;
use FalconERP\Skeleton\Models\Erp\Finance\FinancialAccount;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

class FinancialImport extends BaseModel
{
    use HasFactory;
    use ArchiveModelTrait;

    protected $fillable = [
        'financial_account_id',
        'people_id',
        'bank_type',
        'status',
        'batch_id',
        'total_transactions',
        'transactions_analyzed',
        'transactions_with_people',
        'transactions_pending',
        'total_value',
        'analyzed_at',
        'reviewed_at',
        'imported_at',
    ];

    protected $casts = [
        'total_transactions'       => 'integer',
        'transactions_analyzed'    => 'integer',
        'transactions_with_people' => 'integer',
        'transactions_pending'     => 'integer',
        'total_value'              => 'integer',
        'analyzed_at'              => 'datetime',
        'reviewed_at'              => 'datetime',
        'imported_at'              => 'datetime',
    ];

    // Relationships

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialImportTransaction::class);
    }

    public function files(): MorphMany
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_FINANCE_IMPORT_FILE);
    }

    // Scopes

    #[Scope]
    public function byStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    #[Scope]
    public function pending($query)
    {
        return $query->whereIn('status', ['uploaded', 'analyzing', 'analyzed']);
    }

    #[Scope]
    public function completed($query)
    {
        return $query->where('status', 'completed');
    }

    #[Scope]
    public function failed($query)
    {
        return $query->where('status', 'failed');
    }

    // Accessors

    public function getProgressPercentageAttribute(): float
    {
        if (0 === $this->total_transactions) {
            return 0;
        }

        return round(($this->transactions_analyzed / $this->total_transactions) * 100, 2);
    }

    public function getCanConfirmAttribute(): bool
    {
        return $this->canBeConfirmed();
    }

    public function getCanReviewAttribute(): bool
    {
        return $this->canBeReviewed();
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->isCompleted();
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->isFailed();
    }

    // Helper methods

    public function isAnalyzing(): bool
    {
        return 'analyzing' === $this->status;
    }

    public function isAnalyzed(): bool
    {
        return 'analyzed' === $this->status;
    }

    public function isReviewed(): bool
    {
        return 'reviewed' === $this->status;
    }

    public function isImporting(): bool
    {
        return 'importing' === $this->status;
    }

    public function isCompleted(): bool
    {
        return 'completed' === $this->status;
    }

    public function isFailed(): bool
    {
        return 'failed' === $this->status;
    }

    public function canBeReviewed(): bool
    {
        return 'analyzed' === $this->status;
    }

    public function canBeConfirmed(): bool
    {
        return in_array($this->status, ['analyzed', 'reviewed']);
    }
}