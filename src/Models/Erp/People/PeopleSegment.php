<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class PeopleSegment extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [
        'people_id',
        'name',
        'value',
        'description',
        'bank',
        'agency',
        'current_account',
        'birth_date',
        'marital_status',
        'education_level',
        'gender',
        'skin_color',
        'admission_date',
        'demission_date',
        'contract_type',
        'salary_type',
        'salary_value',
        'payment_day',
        'payment_method',
        'job_title',
        'exame_admission_date',
        'exame_demission_date',

        'uses_transportation_voucher',
        'transportation_voucher_value',
        'uses_food_voucher',
        'food_voucher_value',
        'uses_health_plan',
        'health_plan_value',
        'uses_life_insurance',
        'life_insurance_value',
        'first_job',
        'has_disability',
        'works_simultaneously_in_another_company',

    ];

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    |
    | Here you may specify the others that the model should have with
    |
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {

            foreach ($model->getAttributes() as $key => $value) {
                if (in_array($key, ['id', 'people_id'])) {
                    continue;
                }

                unset($model->attributes[$key]);
                $model->setAttribute('name', $key);
                $model->setAttribute('value', $value);
            }

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

}
