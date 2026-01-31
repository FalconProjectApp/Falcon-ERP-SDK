<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Observers\Finance;

use FalconERP\Skeleton\Models\Erp\Finance\Bill;

class BillObserver
{
    /**
     * Handle the Bill "created" event.
     */
    public function created(Bill $bill): void
    {
        $this->syncTags($bill);
    }

    /**
     * Handle the Bill "updated" event.
     */
    public function updated(Bill $bill): void
    {
        $this->syncTags($bill);
    }

    /**
     * Sincroniza tags a partir dos dados da requisição.
     */
    private function syncTags(Bill $bill): void
    {
        // Verifica se há tags na requisição
        $tags = request()->input('tags', []);

        if (empty($tags)) {
            return;
        }

        // Utiliza o método do trait para sincronizar por nome
        $bill->syncTagsByName($tags);
    }
}
