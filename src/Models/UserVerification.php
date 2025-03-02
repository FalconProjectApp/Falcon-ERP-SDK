<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Notifications\Notifiable;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class UserVerification extends BaseModel
{
    use Notifiable;

    protected $fillable = [
        'verification_code',
    ];
}
