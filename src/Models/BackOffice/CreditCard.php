<?php

namespace FalconERP\Skeleton\Models\BackOffice;

use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCard extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $fillable = [
        'user_id',
        'hash',
        'brand',
        'last_digits',
        'last_used_at',
        'is_main',
    ];

    /**
     * billing function.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
