<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Models\Erp\Finance\BillInstallment;
use FalconERP\Skeleton\Models\Erp\Finance\FinancialImport;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class FinancialImportTransaction extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'financial_import_id',
        'date',
        'value',
        'description',
        'identifier',
        'transaction_type',
        'identified_people_id',
        'identified_match_type',
        'similarity_score',
        'selected_people_id',
        'create_new_people',
        'cnpj',
        'cpf',
        'name',
        'bank_info',
        'status',
        'installment_id',
        'created_people_id',
    ];

    protected $casts = [
        'date'              => 'date',
        'value'             => 'integer',
        'similarity_score'  => 'decimal:4',
        'create_new_people' => 'boolean',
        'bank_info'         => 'array',
    ];

    // Relationships

    public function financialImport(): BelongsTo
    {
        return $this->belongsTo(FinancialImport::class);
    }

    public function identifiedPeople(): BelongsTo
    {
        return $this->belongsTo(People::class, 'identified_people_id');
    }

    public function selectedPeople(): BelongsTo
    {
        return $this->belongsTo(People::class, 'selected_people_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(BillInstallment::class);
    }

    public function createdPeople(): BelongsTo
    {
        return $this->belongsTo(People::class, 'created_people_id');
    }

    // Scopes

    #[Scope]
    public function pending($query)
    {
        return $query->where('status', 'pending');
    }

    #[Scope]
    public function reviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    #[Scope]
    public function imported($query)
    {
        return $query->where('status', 'imported');
    }

    #[Scope]
    public function ignored($query)
    {
        return $query->where('status', 'ignored');
    }

    #[Scope]
    public function withIdentifiedPeople($query)
    {
        return $query->whereNotNull('identified_people_id');
    }

    #[Scope]
    public function withoutIdentifiedPeople($query)
    {
        return $query->whereNull('identified_people_id');
    }

    #[Scope]
    public function byImport($query, int $importId)
    {
        return $query->where('financial_import_id', $importId);
    }

    #[Scope]
    protected function byMatchType(Builder $query, array $params = []): Builder
    {
        return $query->when(
            $this->filtered($params, 'match_type'),
            fn ($query, $params) => $query->whereIn('identified_match_type', $params)
        );
    }

    #[Scope]
    protected function byStatus(Builder $query, array $params = []): Builder
    {
        return $query->when(
            $this->filtered($params, 'status'),
            fn ($query, $params) => $query->whereIn('status', $params)
        );
    }

    // Helper methods

    public function hasIdentifiedPeople(): bool
    {
        return null !== $this->identified_people_id;
    }

    public function hasSelectedPeople(): bool
    {
        return null !== $this->selected_people_id;
    }

    public function needsReview(): bool
    {
        return 'pending' === $this->status && !$this->hasSelectedPeople();
    }

    public function isReviewed(): bool
    {
        return 'reviewed' === $this->status;
    }

    public function isImported(): bool
    {
        return 'imported' === $this->status;
    }

    public function isIgnored(): bool
    {
        return 'ignored' === $this->status;
    }

    public function getDisplayPeopleId(): ?int
    {
        return $this->selected_people_id ?? $this->identified_people_id;
    }

    public function getDisplayPeople(): ?object
    {
        return $this->selectedPeople ?? $this->identifiedPeople;
    }
}