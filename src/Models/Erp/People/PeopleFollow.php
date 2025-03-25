<?php

namespace FalconERP\Skeleton\Models\People;

use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class PeopleFollow extends BaseModel
{
    use HasFactory;
    use Notifiable;
    use SetSchemaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function followable()
    {
        return $this->morphTo();
    }

    public function follower()
    {
        return $this->belongsTo(People::class, 'follower_people_id');
    }
}
