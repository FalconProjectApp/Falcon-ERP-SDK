<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock\Traits\Request;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ProductSegmentTrait
{
    abstract public function segments(): HasMany;

    protected function ean(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'ean')->first()?->value,
        );
    }

    protected function ncm(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'ncm')->first()?->value,
        );
    }

    protected function unitAbbreviation(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'unit_abbreviation')->first()?->value,
        );
    }

    protected function unitDescription(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'unit_description')->first()?->value,
        );
    }
}
