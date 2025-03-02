<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class PurchaseHistory extends BaseModel
{
    use Notifiable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'billing_id',
        'amount',
        'discount',
        'value',
        'invalid_at',
        'obs',
    ];

    /**
     * user function.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * billing function.
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
