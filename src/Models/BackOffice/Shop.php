<?php

namespace FalconERP\Skeleton\Models\BackOffice;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'pgsql';

    protected $fillable = [
        'database_id',
        'slug',
        'searched',
        'last_searched_at',
    ];

    /**
     * billing function.
     */
    public function databases(): BelongsTo
    {
        return $this->belongsTo(Database::class, 'database_id');
    }

    /**
     * Get the slug.
     */
    protected function slug(): Attribute
    {
        return new Attribute(
            set: fn (string $value) => Str::slug($value),
        );
    }
}
