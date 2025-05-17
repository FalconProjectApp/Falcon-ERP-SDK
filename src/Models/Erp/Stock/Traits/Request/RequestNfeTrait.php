<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock\Traits\Request;

use FalconERP\Skeleton\Enums\RequestEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use NFePHP\NFe\Make;
use QuantumTecnology\ValidateTrait\Data;

trait RequestNfeTrait
{
    /**
     * precisa ser um atributo em requestbody.
     */
    private function generateCfop(): string
    {
        $cfopBase = match ($this->requestType->request_type) {
            RequestEnum::REQUEST_TYPE_INPUT  => $this->same_state ? 1 : ($this->same_country ? 2 : 3),
            RequestEnum::REQUEST_TYPE_OUTPUT => $this->same_state ? 5 : ($this->same_country ? 6 : 7),
            default                          => 0,
        };

        return $cfopBase.$this->requestType->natureOperationDefault->operation_type->operationType();
    }

    protected function xml(): Attribute
    {
        $this->loadMissing('requestBodies.stock.product');

        $nfe = new Make();
        $nfe->taginfNFe($this->tag_inf_nfe->toObject());
        $nfe->tagide($this->tag_ide->toObject());
        $nfe->tagemit($this->responsible->tag_emit);
        $nfe->tagenderEmit($this->responsible->tag_ender_emit_dest);
        $nfe->tagdest($this->third->tag_dest);
        $nfe->tagenderDest($this->third->tag_ender_emit_dest);
        $nfe->tagpag($this->tag_pag->toObject());
        $nfe->tagdetPag($this->tag_det_pag->toObject());
        $nfe->tagobsCont($this->tag_obs_cont->toObject());
        $nfe->tagobsFisco($this->tag_obs_fisco->toObject());
        $nfe->taginfAdic($this->tag_inf_adic->toObject());

        $this->requestBodies->each(function ($item, $key) use ($nfe) {
            $itemNumber = $key + 1;

            $nfe->tagprod($item->tag_prod->merge([
                'item' => $itemNumber,
                'CFOP' => $this->generateCfop(),
            ])->toObject());

            $nfe->tagimposto($item->tag_imposto->merge([
                'item' => $itemNumber,
            ])->toObject());

            $nfe->tagICMS($item->tag_icms->toObject());
            // $nfe->tagICMSSN($item->tag_icms_sn->toObject());
            // $nfe->tagPIS($item->tag_pis->toObject());
            // $nfe->tagCOFINS($item->tag_cofins->toObject());
            // $nfe->tagICMSUFDest($item->tag_icms_uf_dest->toObject());
        });

        return Attribute::make(
            get: fn () => $nfe,
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
     * tag_inf_nfe.
     */
    protected function tagInfNfe(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'Id'       => null,
                'versao'   => '4.00',
                'pk_nItem' => null,
            ])
        );
    }

    /**
     * tag_ide.
     */
    protected function tagIde(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                // TODO: fazer a integração com o datahub para ver o ibge do estado
                'cUF'      => $this->c_uf,
                'cNF'      => $this->c_nf,
                'natOp'    => $this->nat_op,
                'indPag'   => 0,
                'mod'      => $this->requestType->natureOperationDefault->serie->model,
                'serie'    => $this->serie,
                'nNF'      => $this->id,
                'dhEmi'    => $this->dh_emi,
                'tpNF'     => $this->tp_nf,
                'idDest'   => $this->id_dest,
                'cMunFG'   => $this->c_mun_fg,
                'tpImp'    => $this->tp_imp,
                'tpEmis'   => $this->tp_emis,
                'cDV'      => $this->c_dv,
                'tpAmb'    => $this->tp_amb,
                'finNFe'   => $this->fin_nfe,
                'indFinal' => $this->ind_final,
                'indPres'  => $this->ind_pres,
                'procEmi'  => $this->proc_emi,
                'verProc'  => $this->ver_proc,
            ])
        );
    }

    /**
     * tag_pag.
     */
    protected function tagPag(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'vTroco' => 0,
            ])
        );
    }

    /**
     * tag_det_pag.
     */
    protected function tagDetPag(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'indPag' => 0,
                'tPag'   => $this->paymentMethod->description,
                'vPag'   => $this->itens_value_total_with_discount,
            ])
        );
    }

    /**
     * tag_obs_cont.
     *
     * NOTA: pode ser usado, por exemplo, para indicar outros
     * destinatários de e-mail, além do próprio destinatário da NFe,
     * como o contador, etc.
     */
    protected function tagObsCont(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'xCampo' => 'Observações',
                'xTexto' => $this->observations,
            ])
        );
    }

    /**
     * tag_obs_fisco.
     */
    protected function tagObsFisco(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'xCampo' => 'Observações',
                'xTexto' => $this->observations,
            ])
        );
    }

    /**
     * tag_inf_adic.
     */
    protected function tagInfAdic(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'infAdFisco' => null,
                'infCpl'     => $this->observations,
            ])
        );
    }

    /**
     * tag_prods.
     */
    protected function tagProds(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestBodies->map(fn ($item, $key) => new Data([
                'item'    => $key + 1,
                'prod'    => $item->tag_prod->toObject(),
                'imposto' => $item->tag_imposto->toObject(),
            ]))
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
     * c_mun_fg.
     * TODO: fazer a integração com o datahub para ver o ibge da cidade.
     */
    protected function cMunFg(): Attribute
    {
        return Attribute::make(
            // $request->responsible->main_address ? '3501608' : null
            get: fn () => $this->responsible->mainAddress?->city->code ?? null,
        );
    }

    /**
     * serie.
     */
    protected function serie(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::padLeft($this->requestType->natureOperationDefault->serie->id, 3, '0'),
        );
    }

    /**
     * cnf.
     * TODO: Consultar se o numero ja nao existe em alguma nota, se existir gerar outro.
     */
    protected function cNf(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::padLeft(mt_rand(0, 99999999), 8, '0'),
        );
    }

    /**
     * c_uf.
     */
    protected function cUf(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->responsible->main_address ? '35' : null,
        );
    }

    /**
     * nat_op.
     */
    protected function natOp(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->description,
        );
    }

    /**
     * tp_amb.
     */
    protected function tpAmb(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->environment_code,
        );
    }

    /**
     * tp_imp.
     * TODO: gerar o print_type_code de acordo com o tipo de impressora.
     */
    protected function tpImp(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->print_type_code ?? 1,
        );
    }

    /**
     * tp_emis.
     * TODO: gerar o emission_type_code de acordo com o tipo de impressora.
     */
    protected function tpEmis(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->emission_type_code ?? 1,
        );
    }

    /**
     * proc_emi.
     */
    protected function procEmi(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->process_code ?? 0,
        );
    }

    /**
     * ver_proc.
     * TODO: gerar o version_code de acordo com o tipo de impressora.
     */
    protected function verProc(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->version_code ?? '1.0',
        );
    }

    /**
     * ind_final.
     * TODO: gerar o ind_final de acordo com o tipo de impressora.
     */
    protected function indFinal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->ind_final ?? 1,
        );
    }

    /**
     * dh_emi.
     */
    protected function dhEmi(): Attribute
    {
        return Attribute::make(
            get: fn () => now()->format('Y-m-d\TH:i:sP'),
        );
    }

    /**
     * tp_nf.
     * TODO: gerar o tp_nf de acordo com o tipo de impressora.
     */
    protected function tpNF(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->tp_nf ?? 1,
        );
    }

    /**
     * c_dv.
     * TODO: gerar o c_dv de acordo com o tipo de impressora.
     */
    protected function cDv(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->c_dv ?? null,
        );
    }

    /**
     * fin_nfe.
     * TODO: gerar o fin_nfe de acordo com o tipo de impressora.
     */
    protected function finNFe(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requestType->natureOperationDefault->serie->fin_nfe ?? 1,
        );
    }
}
