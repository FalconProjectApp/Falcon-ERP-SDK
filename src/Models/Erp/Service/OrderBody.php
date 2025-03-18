<?php

namespace FalconERP\Skeleton\Models\Erp\Service;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OrderBody extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'service_id',
        'worked_at',
        'started_at',
    ];

    protected $appends = [];

    public $allowedIncludes = [];

    public function service(bool $withTrashed = false)
    {
        return $this->belongsTo(Service::class)
            ->when($withTrashed, fn ($query) => $query->withTrashed());
    }

    public function serviceWithTrashed()
    {
        return $this->service(true);
    }

    /**
     * Get the workedAt.
     */
    protected function workedAt(): Attribute
    {
        return new Attribute(
            get: function ($worked_at) {
                list($hours, $minutes, $seconds) = explode(':', $worked_at);
                if (Str::contains($seconds, '-')) {
                    list($seconds, $trash) = explode('-', $seconds);
                }

                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
        );
    }
}
