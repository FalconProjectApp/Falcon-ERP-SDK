<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People\Faq;

use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class ForumCategory extends BaseModel
{
    use SetSchemaTrait;

    protected $connection = 'pgsql';

    protected $table = 'forum_categories';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'topics_count',
        'order',
        'is_active',
    ];

    protected $casts = [
        'topics_count' => 'integer',
        'order'        => 'integer',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // Relationships

    public function topics(): HasMany
    {
        return $this->hasMany(FaqTopic::class, 'category_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
