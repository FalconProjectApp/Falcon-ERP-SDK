<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class RequestBody extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'stock_id',
        'value',
        'discount',
        'amount',
    ];

    public $allowedIncludes = [];

    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class);
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

    protected function xProd(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->description,
        );
    }
    protected function cBarra(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock->product->bar_code,
        );
    }
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
    protected function uCom(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function qCom(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function vUnCom(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function vProd(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function cEANTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function uTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function qTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
    protected function vUnTrib(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
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
    protected function xPed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }
}
