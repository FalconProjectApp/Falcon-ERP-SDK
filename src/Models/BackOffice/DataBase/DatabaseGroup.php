<?php

namespace FalconERP\Skeleton\Models\BackOffice\DataBase;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DatabaseGroup extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $connection = 'pgsql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
    ];

    /**
     * billing function.
     */
    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }
}
