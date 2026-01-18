<?php

namespace FalconERP\Skeleton\Models\Erp\Service;

use FalconERP\Skeleton\Enums\CacheEnum;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Service\Traits\Order\OrderNfseTrait;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\EventDispatcherObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
    EventDispatcherObserver::class,
])]
class Order extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use OrderNfseTrait;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'responsible_id',
        'taker_id',
        'provider_id',
        'service_time',
        'status',
        'scheduled_at',
        'obs',
    ];

    protected $caches = [
        CacheEnum::KEY_ORDER_TOTAL_COUNT,
        CacheEnum::KEY_ORDER_TRASHED_COUNT,
        CacheEnum::KEY_ORDER_STATUS_OPEN_COUNT,
        CacheEnum::KEY_ORDER_STATUS_IN_PROGRESS_COUNT,
        CacheEnum::KEY_ORDER_STATUS_PAUSE_COUNT,
        CacheEnum::KEY_ORDER_STATUS_CLOSEDS_COUNT,
    ];

    protected $appends = [];

    public function orderBodies(bool $withTrashed = false)
    {
        return $this->hasMany(OrderBody::class)
            ->when($withTrashed, fn ($query) => $query->withTrashed());
    }

    public function orderBodiesWithTrasheds()
    {
        return $this->orderBodies(true);
    }

    public function taker()
    {
        return $this->belongsTo(People::class, 'taker_id');
    }

    public function provider()
    {
        return $this->belongsTo(People::class, 'provider_id');
    }

    public function responsible()
    {
        return $this->belongsTo(People::class, 'responsible_id');
    }

    /**
     * Get the amountTotal.
     */
    protected function timeTotal(): Attribute
    {
        $totalSeconds = 0;

        $this->orderBodies(true)
            ->with(['service' => function ($query) {
                return $query->withTrashed();
            }])->each(function ($orderBody) use (&$totalSeconds) {
                list($hours, $minutes, $seconds) = explode(':', $orderBody->worked_at);

                $totalSeconds += $hours * 3600;
                $totalSeconds += $minutes * 60;

                $totalSeconds += $seconds;
            });

        if ($totalSeconds > 0) {
            $hours   = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds / 60) % 60);
            $seconds = $totalSeconds % 60;
        } else {
            $hours   = 0;
            $minutes = 0;
            $seconds = 0;
        }

        return new Attribute(
            get: fn () => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
        );
    }

    /**
     * Get the amountTotal.
     */
    protected function valueTotal(): Attribute
    {
        $total = 0;

        $this->orderBodies(true)
            ->with(['service' => function ($query) {
                return $query->withTrashed();
            }])->each(function ($orderBody) use (&$total) {
                $totalSeconds = 0;

                list($hours, $minutes, $seconds) = explode(':', $orderBody->service->service_time);

                $totalSeconds += $hours * 3600;
                $totalSeconds += $minutes * 60;
                $totalSeconds += $seconds;

                $valuePart = $totalSeconds > 0 ? $orderBody->service->value / $totalSeconds : $totalSeconds;

                $totalSeconds                    = 0;
                list($hours, $minutes, $seconds) = explode(':', $orderBody->worked_at);

                $totalSeconds += $hours * 3600;
                $totalSeconds += $minutes * 60;
                $totalSeconds += $seconds;
                $total = $valuePart * $totalSeconds;
            });

        return new Attribute(
            get: fn () => (int) $total,
        );
    }

    #[Scope]
    public function byStatus(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'status'), function ($query, $params) {
                $query->whereIn('status', $params);
            });
    }
}
