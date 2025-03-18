<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'title',
        'description',
        'type',
        'people_id',
    ];

    public function notificationView()
    {
        return $this->belongsTo(NotificationView::class);
    }
}
