<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use FalconERP\Skeleton\Models\BackOffice\GiftCode;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use FalconERP\Skeleton\Models\BackOffice\CreditCard;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use FalconERP\Skeleton\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * TODO: extender extends Authenticatable
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use HasApiTokens;
    use HasFactory;
    use MustVerifyEmail;
    use Notifiable;
    use SoftDeletes;

    protected $connection = 'pgsql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'gift_code_id',
        'payment_customer_hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * UserSessions function.
     */
    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get the access tokens that belong to model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function withLastUsedAt()
    {
        $this->tokens->each(function ($token) {
            $token->last_used_at = Carbon::now();
            $token->save();
        });
    }

    /**
     * CreditCards function.
     */
    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }

    /**
     * Database function.
     *
     * @return HasMany
     */
    public function databasesAccess(): BelongsToMany
    {
        return $this
            ->belongsToMany(Database::class, DatabasesUsersAccess::class)
            ->withPivot([
                'base_people_id',
                'is_active',
                'environment',
            ]);
    }

    public function databasesUsersAccess(): HasMany
    {
        return $this->hasMany(DatabasesUsersAccess::class)
            ->where('database_id', tenant()->id ?? null);
    }

    /**
     * purchaseHistories function.
     */
    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(PurchaseHistory::class);
    }

    public function giftCodes(): HasMany
    {
        return $this->hasMany(GiftCode::class, 'owner_id');
    }

    public function giftCodeUsed(): BelongsTo
    {
        return $this->belongsTo(GiftCode::class, 'gift_code_id');
    }
}
