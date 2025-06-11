<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Notifications\Notifiable;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

class Email extends BaseModel
{
    use ActionTrait;
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

        /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the actions that the model should have with
    |
    */

    protected function setActions(): array
    {
        return [
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canRestore(): bool
    {
        return $this->trashed();
    }

    private function canUpdate(): bool
    {
        return !$this->trashed();
    }

    private function canDelete(): bool
    {
        return !$this->trashed();
    }
}
