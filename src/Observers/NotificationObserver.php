<?php

namespace FalconERP\Skeleton\Observers;

use App\Events\ModelRestore;
use App\Events\ModelUpdated;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ValidateTrait\Data;

/**
 * @deprecated Descontinuado pois esta logica foi migrada para o pacote da quantum.
 * @see QuantumTecnology\ModelBasicsExtension\Observers\NotificationObserver
 * @since 1.7.21
 */
class NotificationObserver
{
    public function deleted(Model $model)
    {
        /*    event(new ModelUpdated(new Data([
               'model'   => $model,
               'message' => "A pessoa {$model->name} foi movida para a lixeira.",
           ]))); */
    }

    public function restored(Model $model)
    {
        /* event(new ModelRestore(new Data([
            'model'   => $model,
            'message' => "A pessoa {$model->name} foi restaurada.",
        ]))); */
    }
}
