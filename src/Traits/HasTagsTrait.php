<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Traits;

use FalconERP\Skeleton\Models\Erp\Finance\Tag;

trait HasTagsTrait
{
    /**
     * Sincroniza tags a partir de nomes, criando-as se não existirem.
     *
     * @param array $tagNames Array de nomes de tags
     * @return void
     */
    public function syncTagsByName(array $tagNames): void
    {
        $tagIds = collect($tagNames)->map(function ($tagName) {
            // Criar ou buscar a tag pelo nome
            $tag = Tag::firstOrCreate(
                ['name' => trim($tagName)],
                ['color' => $this->generateRandomColor()]
            );

            return $tag->id;
        })->toArray();

        // Sincronizar os IDs das tags
        $this->tags()->sync($tagIds);
    }

    /**
     * Gera uma cor hexadecimal aleatória para a tag.
     *
     * @return string
     */
    private function generateRandomColor(): string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}
