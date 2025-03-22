<?php

namespace App\Models\BackOffice;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Payment extends BaseModel implements AuditableContract
{
    use Notifiable;
    use SoftDeletes;
    use Auditable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'method',
        'credit_card_id',
        'environment',
        'currency',
        'gift_code_id',
        'value',
        'owner_bonus_value',
        'client_bonus_value',

        'customer',
        'items',
        'payments',
        'response',

        'order_hash',
        'order_status',
        'charge_hash',
        'charge_status',
        'transaction_hash',
        'transaction_status',
        'transaction_response',

        'status',
    ];

    /**
     * Customer function.
     */
    public function customerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Customer function.
     */
    public function giftCode(): BelongsTo
    {
        return $this->belongsTo(GiftCode::class);
    }
}
