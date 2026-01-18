<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Service\Traits\Order;

use Hadder\NfseNacional\Dps;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Response;
use QuantumTecnology\ValidateTrait\Data;
use stdClass;

trait OrderNfseTrait
{
    public function xml(): Attribute
    {
        if (isset($this->attributeCastCache['xml'])) {
            return Attribute::make(
                get: fn () => $this->attributeCastCache['xml'],
            );
        }

        abort_if(
            !class_exists(Dps::class),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            __('A classe Dps do pacote Hadder\NfseNacional não foi encontrada. Verifique se o pacote está instalado corretamente.')
        );

        $std = new stdClass();
        /*
         * INFORMAÇÕES
         */
        $std->infDPS           = new stdClass();
        $std->infDPS->tpAmb    = $this->tp_amb;
        $std->infDPS->dhEmi    = $this->dh_emi;
        $std->infDPS->verAplic = $this->ver_aplic;
        $std->infDPS->serie    = $this->serie;
        $std->infDPS->nDPS     = $this->n_dps;
        $std->infDPS->dCompet  = $this->d_compet;
        $std->infDPS->tpEmit   = $this->tp_emit;
        $std->infDPS->cLocEmi  = $this->c_loc_emi;

        $std->infDPS->subst = $this->subst;
        $std->infDPS->prest = $this->prest;
        $std->infDPS->toma  = $this->toma;
        //
        // $serv = clone $this->servicos;
        //
        // $std->infDPS->serv = $serv->transform(function ($servico) {
        //    return (object) [
        //        'comExt'    => $servico->com_ext,
        //        'infoCompl' => $servico->info_compl,
        //        'locPrest'  => $servico->loc_prest,
        //        'cServ'     => $servico->c_serv,
        //    ];
        // })->first();
        //
        // $primeiroServico      = $this->servicos->first();
        // $std->infDPS->valores = $primeiroServico ? $primeiroServico->valores : null;

        return new Attribute(
            get: fn () => new Dps($std),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tags
    |--------------------------------------------------------------------------
    |
    | Here you may specify the tags that should be cast to native types.
    |
    */

    /**
     * tp_amb.
     * 1 - Produção;
     * 2 - Homologação;.
     */
    protected function tpAmb(): Attribute
    {
        return Attribute::make(
            // get: fn () => $this->orderType->natureOperationDefault->serie->environment_code,
            get: fn () => 2,
        );
    }

    /**
     * dh_emi.
     * Data e hora da emissão da DPS.
     * AAAA-MM-DDThh:mm:ssTZD.
     */
    protected function dhEmi(): Attribute
    {
        return Attribute::make(
            get: fn () => now()->subMinute()->format('Y-m-d\TH:i:sP'),
        );
    }

    /**
     * ver_aplic.
     */
    protected function verAplic(): Attribute
    {
        return new Attribute(
            get: fn () => $this->ver_aplic ?? '1.00',
        );
    }

    /**
     * serie.
     * Série da DPS.
     */
    protected function serie(): Attribute
    {
        return new Attribute(
            // get: fn () => $this->empresa->configNota?->numero_serie_nfse,
            get: fn () => 1,
        );
    }

    /**
     * n_dps.
     * Série da DPS.
     */
    protected function nDps(): Attribute
    {
        return new Attribute(
            /* get: fn (): int => match ($this->ambiente) {
                1       => $this->ultimo_numero_nfse_producao ?? 0,
                2       => $this->ultimo_numero_nfse_homologacao ?? 0,
                default => 0,
            } + 1, */
            get: fn () => 1,
        );
    }

    /**
     * d_compet.
     * Data de competência da prestação do serviço. Ano, Mês e Dia (AAAA-MM-DD).
     */
    protected function dCompet(): Attribute
    {
        return new Attribute(
            get: fn () => now()->format('Y-m-d'),
        );
    }

    /**
     * tp_emit.
     * Tipo de emitente da DPS: 1 - Prestador; 2 - Tomador; 3 - Intermediário;.
     */
    protected function tpEmit(): Attribute
    {
        return new Attribute(
            get: fn () => 1,
        );
    }

    /**
     * c_loc_emi.
     * Código IBGE 7 dígitos da cidade emissora da NFS-e.
     */
    protected function cLocEmi(): Attribute
    {
        return new Attribute(
            get: fn () => 1234567,
        );
    }

    /*
     * subst.
     * Dados da Substituição Tributária (se houver)
     */
    protected function subst(): Attribute
    {
        return new Attribute(
            get: fn (): ?object => match (false) {
                true => (object) [
                    'chSubstda' => 'DPS000000000000000000000000000000000000000000', // Chave de Acesso da NFS-e a ser substituída.
                    'cMotivo'   => '01', // 01 - Desenquadramento de NFS-e do Simples Nacional; 02 - Enquadramento de NFS-e no Simples Nacional; 03 - Inclusão Retroativa de Imunidade/Isenção para NFS-e; 04 - Exclusão Retroativa de Imunidade/Isenção para NFS-e; 05 - Rejeição de NFS-e pelo tomador ou pelo intermediário se responsável pelo recolhimento do tributo; 99 - Outros;
                    'xMotivo'   => 'Descreva o motivo', // Descrição do motivo da substituição quando cMotivo = 9
                ],
                default => null
            },
        );
    }

    /**
     * prest.
     */
    protected function prest(): Attribute
    {
        return new Attribute(
            get: fn (): ?object => $this->provider ?? null,
        );
    }

    /**
     * toma.
     */
    protected function toma(): Attribute
    {
        return new Attribute(
            get: fn (): ?object => $this->taker ?? null,
        );
    }
}
