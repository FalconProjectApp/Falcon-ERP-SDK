<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Billing extends BaseModel
{
    use Notifiable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'title',
        'description',
        'rule',
        'value',
    ];

    /**
     * billing function.
     */
    public function billing(): BelongsToMany
    {
        return $this->belongsToMany(BillingPack::class, 'billing_pack_billing_pivot');
    }

    /**
     * Get the valueTotal.
     */
    protected function valueTotal(): Attribute
    {
        return new Attribute(
            get: fn () => $this->value * (float) $this->discount,
        );
    }
}
