<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\Archive;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;

class Email extends BaseModel
{
    use HasFactory;
    use Notifiable;
    use SetSchemaTrait;

    protected $fillable = [
        'responsible_people_id',
        'email_sender',
        'email_receiver',
        'subject',
        'content',
    ];

    public $allowedIncludes = [];

    /**
     * Archives function.
     */
    public function archives(): MorphMany
    {
        return $this->morphMany(Archive::class, 'archivable');
    }

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
