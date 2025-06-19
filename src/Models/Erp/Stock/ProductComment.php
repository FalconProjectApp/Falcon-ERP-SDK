<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use FalconERP\Skeleton\Database\Factories\ProductCommentFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class ProductComment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'comment',
        'origin',
    ];

    protected static function newFactory()
    {
        return ProductCommentFactory::new();
    }

    public function productComments(): HasMany
    {
        return $this->hasMany($this);
    }

    public function productCommentParent(): BelongsTo
    {
        return $this->belongsTo($this);
    }
}
