<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

class Email extends BaseModel
{
    use HasFactory;
    use Notifiable;
    use SetSchemaTrait;
    use ArchiveModelTrait;

    protected $fillable = [
        'responsible_people_id',
        'email_sender',
        'email_receiver',
        'subject',
        'content',
    ];

    public $allowedIncludes = [];

    /**
     * attachments function.
     */
    public function attachments()
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_EMAIL_FILE);
    }

    public static function booting(): void
    {
        static::addGlobalScope('withRelations', function ($query) {
            $query->with([
                'attachments',
            ]);
        });
    }
}
