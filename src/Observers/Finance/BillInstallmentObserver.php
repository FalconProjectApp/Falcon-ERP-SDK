<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Observers\Finance;

use FalconERP\Skeleton\Models\Erp\Finance\BillInstallment;

class BillInstallmentObserver
{
    /**
     * Handle the BillInstallment "created" event.
     */
    public function created(BillInstallment $billInstallment): void
    {
        $this->syncTags($billInstallment);
    }

    /**
     * Handle the BillInstallment "updated" event.
     */
    public function updated(BillInstallment $billInstallment): void
    {
        $this->syncTags($billInstallment);
    }

    /**
     * Sincroniza tags a partir dos dados da requisição.
     */
    private function syncTags(BillInstallment $billInstallment): void
    {
        // Verifica se há tags na requisição
        $tags = request()->input('tags', []);

        if (empty($tags)) {
            return;
        }

        // Utiliza o método do trait para sincronizar por nome
        $billInstallment->syncTagsByName($tags);
    }
}
