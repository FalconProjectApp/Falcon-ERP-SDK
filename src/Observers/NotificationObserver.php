<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Observers;

use FalconERP\Skeleton\Models\Erp\People\Notification;
use Illuminate\Database\Eloquent\Model;

class NotificationObserver
{
    public function updated(Model $model): void
    {
        $this->handle($model, 'updated');
    }

    public function deleted(Model $model)
    {
        $this->handle($model, 'deleted');
    }

    public function restored(Model $model)
    {
        $this->handle($model, 'restored');
    }

    private function handle(Model $model, string $event): void
    {
        $modelName  = class_basename($model);
        $recordName = method_exists($model, 'getDisplayName') ? $model->getDisplayName() : ($model->name ?? $model->id);

        $titles = [
            'updated'  => "Atualização em {$modelName}: {$recordName}",
            'deleted'  => "{$modelName} removido: {$recordName}",
            'restored' => "{$modelName} restaurado: {$recordName}",
        ];

        $contents = [
            'updated'  => "O registro <strong>{$recordName}</strong> do tipo <strong>{$modelName}</strong> foi atualizado por <strong>" . (people()->name ?? 'um usuário') . '</strong>.',
            'deleted'  => "O registro <strong>{$recordName}</strong> do tipo <strong>{$modelName}</strong> foi removido por <strong>" . (people()->name ?? 'um usuário') . '</strong>.',
            'restored' => "O registro <strong>{$recordName}</strong> do tipo <strong>{$modelName}</strong> foi restaurado por <strong>" . (people()->name ?? 'um usuário') . '</strong>.',
        ];

        $model->followers()->chunk(20, function ($followers) use ($model, $event, $titles, $contents) {
            foreach ($followers as $follower) {
                if ($follower->id === people()->id) {
                    continue;
                }

                Notification::create([
                    'responsible_people_id' => people()->id,
                    'notifiable_type'       => $model->getTable() . "_{$event}",
                    'notifiable_id'         => $follower->id,
                    'title'                 => $titles[$event] ?? 'Notificação',
                    'content'               => $contents[$event] ?? '',
                ]);
            }
        });
    }
}
