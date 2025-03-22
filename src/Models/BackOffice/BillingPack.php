<?php

namespace App\Models\BackOffice;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class BillingPack extends BaseModel
{
    use Notifiable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'title',
        'description',
        'discount',
    ];

    /**
     * billingPack function.
     */
    public function billings(): BelongsToMany
    {
        return $this->belongsToMany(Billing::class, 'billing_pack_billing_pivots', 'billing_pack_id', 'billing_id')
            ->withPivot([
                'amount',
                'valid_months',
            ]);
    }

    /**
     * Get the value.
     */
    protected function value(): Attribute
    {
        $total = 0;
        foreach ($this->billings as $billing) {
            $total += (float) $billing->pivot->amount * (float) $billing->value;
        }

        return new Attribute(
            get: fn () => $total,
        );
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
