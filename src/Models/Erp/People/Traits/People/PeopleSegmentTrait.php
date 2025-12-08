<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People\Traits\People;

use FalconERP\Skeleton\Enums\People\PeopleCrtEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait PeopleSegmentTrait
{
    abstract public function segments(): HasMany;

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    protected function bank(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    protected function agency(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'agency')->first()?->value,
        );
    }

    protected function currentAccount(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'current_account')->first()?->value,
        );
    }

    protected function birthDate(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'birth_date')->first()?->value,
        );
    }

    protected function maritalStatus(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'marital_status')->first()?->value,
        );
    }

    protected function educationLevel(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'education_level')->first()?->value,
        );
    }

    protected function gender(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'gender')->first()?->value,
        );
    }

    protected function skinColor(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'skin_color')->first()?->value,
        );
    }

    protected function admissionDate(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'admission_date')->first()?->value,
        );
    }

    protected function demissionDate(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'demission_date')->first()?->value,
        );
    }

    protected function contractType(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'contract_type')->first()?->value,
        );
    }

    protected function salaryType(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'salary_type')->first()?->value,
        );
    }

    protected function salaryValue(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'salary_value')->first()?->value,
        );
    }

    protected function paymentDay(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'payment_day')->first()?->value,
        );
    }

    protected function paymentMethod(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'payment_method')->first()?->value,
        );
    }

    protected function jobTitle(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn () => $this->segments->where('name', 'job_title')->first()?->value,
        );
    }

    protected function exameAdmissionDate(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?string => $this->segments->where('name', 'exame_admission_date')->first()?->value,
        );
    }

    protected function exameDemissionDate(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?string => $this->segments->where('name', 'exame_demission_date')->first()?->value,
        );
    }

    protected function usesTransportationVoucher(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'uses_transportation_voucher')->first()?->value,
        );
    }

    protected function transportationVoucherValue(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?int => (int) $this->segments->where('name', 'transportation_voucher_value')->first()?->value,
        );
    }

    protected function usesFoodVoucher(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'uses_food_voucher')->first()?->value,
        );
    }

    protected function foodVoucherValue(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?int => (int) $this->segments->where('name', 'food_voucher_value')->first()?->value,
        );
    }

    protected function usesHealthPlan(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'uses_health_plan')->first()?->value,
        );
    }

    protected function healthPlanValue(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?int => (int) (int) $this->segments->where('name', 'health_plan_value')->first()?->value,
        );
    }

    protected function usesLifeInsurance(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'uses_life_insurance')->first()?->value,
        );
    }

    protected function lifeInsuranceValue(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?int => (int) $this->segments->where('name', 'life_insurance_value')->first()?->value,
        );
    }

    protected function firstJob(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'first_job')->first()?->value,
        );
    }

    protected function hasDisability(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'has_disability')->first()?->value,
        );
    }

    protected function worksSimultaneouslyInAnotherCompany(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): bool => (bool) $this->segments->where('name', 'works_simultaneously_in_another_company')->first()?->value,
        );
    }

    protected function crt(): Attribute
    {
        $this->loadMissing('segments');

        return Attribute::make(
            get: fn (): ?int => $this->segments->where('name', 'crt')->first()?->value ?? PeopleCrtEnum::REGIME_MEI->value,
        );
    }
}
