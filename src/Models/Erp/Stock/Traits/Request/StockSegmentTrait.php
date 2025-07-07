<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock\Traits\Request;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StockSegmentTrait
{
    abstract public function segments(): HasMany;

    protected function dun(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'dun')->first()?->value,
        );
    }
}
