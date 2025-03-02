<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class PaymentHistory extends BaseModel
{
    use Notifiable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'value',
    ];

    /**
     * user function.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
