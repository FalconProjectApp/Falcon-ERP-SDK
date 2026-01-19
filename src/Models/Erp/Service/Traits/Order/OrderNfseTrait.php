<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Service\Traits\Order;

use Hadder\NfseNacional\Dps;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Response;
use QuantumTecnology\ValidateTrait\Data;

trait OrderNfseTrait
{
    abstract public function taker(): BelongsTo;

    abstract public function provider(): BelongsTo;

    abstract public function orderBodies(bool $withTrashed = false): HasMany;

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

        $std = new \stdClass();
        /*
         * INFORMAÇÕES
         */
        $std->infDPS           = new \stdClass();
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
                default => null,
            },
        );
    }

    /*
     * Dados do Prestador do Serviço
     *
     * IM - Número de inscrição municipal do tomaador do serviço.
     */
    public function prest(): Attribute
    {
        dd($this);
        return new Attribute(
            // get: fn (): ?object => $this->provider ?? null,
            get: fn (): ?object => (object) [
                mb_strlen(preg_replace('/\D/', '', $this->cpf_cnpj)) > 11 ? 'CNPJ' : 'CPF' => preg_replace('/\D/', '', $this->cpf_cnpj),
                'NIF'                                                                      => null, // Número de identificação fiscal fornecido por órgão de administração tributária no exterior.
                'cNaoNIF'                                                                  => null, // Motivo para não informação do NIF: 0 - Não informado na nota de origem; 1 - Dispensado do NIF; 2 - Não exigência do NIF;
                'CAEPF'                                                                    => null, // Número do Cadastro de Atividade Econômica da Pessoa Física (CAEPF) do tomaador do serviço.
                'IM'                                                                       => match (false) {
                    true == empty($this->tributacao?->inscricao_municipal) => $this->tributacao?->inscricao_municipal,
                    default                                                => null,
                },
                // 'xNome'   => $this->razao_social,
                'fone'  => preg_replace('/\D/', '', $this->telefone),
                'email' => $this->email,
                // 'end'     => $this->address,
                'regTrib' => $this->reg_trib,
            ],
        );
    }

    public function address(): Attribute
    {
        return new Attribute(
            get: fn () => (object) [
                'xLgr'    => $this->rua,
                'nro'     => $this->numero,
                'xCpl'    => null,
                'xBairro' => $this->bairro,
                'endNac'  => $this->end_nac,
                // 'endExt'  => $this->end_ext,
            ],
        );
    }

    /*
     * Dados do Tomador do Serviço
     */
    public function toma(): Attribute
    {
        return new Attribute(
            // get: fn (): ?object => $this->taker ?? null,
            get: fn (): ?object => (object) [
                'CNPJ' => match (mb_strlen(preg_replace('/\D/', '', $this->cpf_cnpj))) {
                    14      => preg_replace('/\D/', '', $this->cpf_cnpj),
                    default => null,
                },
                'CPF' => match (mb_strlen(preg_replace('/\D/', '', $this->cpf_cnpj))) {
                    11      => preg_replace('/\D/', '', $this->cpf_cnpj),
                    default => null,
                },
                'NIF'     => null, // Número de identificação fiscal fornecido por órgão de administração tributária no exterior.
                'cNaoNIF' => null, // Motivo para não informação do NIF: 0 - Não informado na nota de origem; 1 - Dispensado do NIF; 2 - Não exigência do NIF;
                'CAEPF'   => null, // Número do Cadastro de Atividade Econômica da Pessoa Física (CAEPF) do tomaador do serviço.
                'IM'      => null, // Número de inscrição municipal do tomaador do serviço.
                'xNome'   => $this->razao_social,
                'fone'    => $this->telefone,
                'email'   => $this->email,
                'end'     => $this->end,
            ],
        );
    }

    public function end(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (object) [
                'xLgr'    => $this->rua,
                'nro'     => $this->numero,
                'xCpl'    => $this->complemento,
                'xBairro' => $this->bairro,
                'endNac'  => $this->end_nac,
                'endExt'  => match (false) {
                    true    => $this->end_ext,
                    default => null,
                },
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    /**
     * has_errors.
     */
    protected function hasErrors(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => count($this->errors) > 0,
        );
    }

    /**
     * errors.
     */
    protected function errors(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->xml->getErrors() ?? false,
        );
    }
}
