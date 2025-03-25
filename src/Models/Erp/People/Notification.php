<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Notification extends BaseModel
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'title',
        'content',
        'responsible_people_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owning notifiable model.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    public function notificationView()
    {
        return $this->belongsTo(NotificationView::class);
    }
}
