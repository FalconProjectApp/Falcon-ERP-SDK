<?php

namespace FalconERP\Skeleton\Models\Erp;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Archive extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'main',
        'active',
        'order',
        'key',
    ];

    public $allowedIncludes = [];

    public function archivable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the formated s3Key.
     */
    protected function UrlKey(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value ? Storage::url($value) : null,
        );
    }

    /**
     * Get the formated url.
     */
    protected function url(): Attribute
    {
        return new Attribute(
            get: fn () => $this->key ? Storage::url($this->key) : null,
        );
    }

    /**
     * Get the formated base64.
     */
    protected function temporaryUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->key ? Storage::temporaryUrl($this->key, Carbon::now()->addMinutes(60)) : null,
        );
    }

    /**
     * Get the formated mime.
     */
    protected function mime(): Attribute
    {
        return new Attribute(
            get: fn () => $this->key ? Storage::mimeType($this->key) : null,
        );
    }

    /**
     * Get the formated mime.
     */
    protected function size(): Attribute
    {
        return new Attribute(
            get: fn () => $this->key ? Storage::size($this->key) : null,
        );
    }

    /**
     * Get the formated base64.
     */
    protected function base64(): Attribute
    {
        return new Attribute(
            get: fn () => $this->key ? base64_encode(Storage::get($this->key)) : null,
        );
    }
}
