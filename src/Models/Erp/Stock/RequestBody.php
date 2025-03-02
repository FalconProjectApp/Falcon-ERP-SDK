<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
