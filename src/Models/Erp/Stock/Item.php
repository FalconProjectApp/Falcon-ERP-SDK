<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ValidateTrait\Data;

class Item extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $table = 'itens';

    protected $fillable = [
        'stock_id',
        'value',
        'discount',
        'amount',
    ];

    protected $casts = [
        'stock_id' => 'integer',
        'value'    => 'integer',
        'discount' => 'integer',
        'amount'   => 'integer',
    ];

    public $allowedIncludes = [];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
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
     * x_prod.
     */
    protected function xProd(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::limit($this->stock->product->description, 120),
        );
    }

    /**
     * c_barra.
     */
    protected function cBarra(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->bar_code,
        );
    }

    /**
     * c_prod.
     */
    protected function cProd(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->id,
        );
    }

    protected function cest(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->cest,
        );
    }

    protected function cfop(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    /**
     * u_com.
     */
    protected function uCom(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->volumeType->initials,
        );
    }

    /**
     * q_com.
     */
    protected function qCom(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount,
        );
    }

    /**
     * v_un_com.
     */
    protected function vUnCom(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format(($this->value - $this->discount) / $this->amount / 100, 10, '.', ''),
        );
    }

    /**
     * v_prod.
     */
    protected function vProd(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->amount * $this->value) / 100,
        );
    }

    /**
     * c_ean.
     */
    protected function cEAN(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->bar_code,
        );
    }

    /**
     * c_ean_trib.
     */
    protected function cEANTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->bar_code,
        );
    }

    /**
     * u_trib.
     */
    protected function uTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->volumeType->initials,
        );
    }

    /**
     * q_trib.
     */
    protected function qTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount,
        );
    }

    /**
     * v_un_trib.
     */
    protected function vUnTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->value / 100, 10, '.', ''),
        );
    }

    protected function vFrete(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    protected function vSeg(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    protected function vDesc(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    protected function vOutro(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    /**
     * x_ped.
     */
    protected function xPed(): Attribute
    {
        return Attribute::make(
            get: fn () => str_pad($this->id, 15, '0', STR_PAD_LEFT),
        );
    }

    /**
     * n_item_ped.
     */
    protected function nItemPed(): Attribute
    {
        $this->load('request');

        return Attribute::make(
            get: fn () => str_pad($this->request->itens->search(fn ($item) => $item->id === $this->id) + 1, 5, '0', STR_PAD_LEFT),
        );
    }

    /**
     * ncm.
     */
    protected function ncm(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->ncm,
        );
    }

    /**
     * tag_prod.
     */
    protected function tagProd(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'cProd'    => $this->c_prod,
                'cEAN'     => $this->c_ean,
                'cBarra'   => $this->c_barra,
                'xProd'    => $this->x_prod,
                'NCM'      => $this->ncm,
                'uCom'     => $this->u_com,
                'qCom'     => $this->q_com,
                'vUnCom'   => $this->v_un_com,
                'vProd'    => $this->v_prod,
                'cEANTrib' => $this->c_ean_trib,
                // TODO: Verificar a unidade tributavel e a comerciavel
                'uTrib'    => $this->u_trib,
                'qTrib'    => $this->q_trib,
                'vUnTrib'  => $this->v_un_trib,
                'indTot'   => 1,
                'xPed'     => $this->x_ped,
                'nItemPed' => $this->n_item_ped,
            ]),
        );
    }

    /**
     * tag_Icms.
     */
    protected function tagIcms(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'orig'     => $this->stock->product->ncm->origin ?? 0,
                'CST'      => $this->stock->product->ncm->cst ?? 0,
                'modBC'    => 0,
                'vBC'      => 0,
                'pICMS'    => 0,
                'vICMS'    => 0,
                'modBCST'  => 0,
                'pMVAST'   => 0,
                'pRedBCST' => 0,
                'vBCST'    => 0,
                'pICMSST'  => 0,
                'vICMSST'  => 0,
            ]),
        );
    }

    /**
     * tag_imposto.
     */
    protected function tagImposto(): Attribute
    {
        return Attribute::make(
            get: fn () => new Data([
                'vTotTrib' => 0,
            ]),
        );
    }
}
