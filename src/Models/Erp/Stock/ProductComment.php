<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductComment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'comment',
        'origin',
    ];

    public function productComments(): HasMany
    {
        return $this->hasMany($this);
    }

    public function productCommentParent(): BelongsTo
    {
        return $this->belongsTo($this);
    }
}
