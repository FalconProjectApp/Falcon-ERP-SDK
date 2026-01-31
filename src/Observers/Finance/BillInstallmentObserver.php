<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Observers\Finance;

use FalconERP\Skeleton\Models\Erp\Finance\BillInstallment;

class BillInstallmentObserver
{
    /**
     * Handle the BillInstallment "saved" event (after created or updated).
     */
    public function saved(BillInstallment $billInstallment): void
    {
        $this->syncTags($billInstallment);
    }

    /**
     * Sincroniza tags a partir dos dados da requisição.
     */
    private function syncTags(BillInstallment $billInstallment): void
    {
        // Tenta pegar tags de data() ou request()
        $tags = [];
        
        if (function_exists('data') && data() && data()->has('tags')) {
            $tags = data('tags');
        } elseif (request()->has('tags')) {
            $tags = request()->input('tags', []);
        }

        if (empty($tags) || !is_array($tags)) {
            return;
        }

        // Utiliza o método do trait para sincronizar por nome
        $billInstallment->syncTagsByName($tags);
    }
}
