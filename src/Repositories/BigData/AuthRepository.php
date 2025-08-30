<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Repositories\BigData;

use FalconERP\Skeleton\Models\Erp\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class AuthRepository
{
    public bool $success          = false;
    public int $http_code         = 0;
    public string $message        = 'not found';
    public ?string $cnpj          = null;
    public array | object $errors = [];
    public array | object $data   = [];
    private string $urlApi;

    public int $timeout = 30;

    public function __construct()
    {
        $this->urlApi = sprintf(
            '%s/auth/v1',
            config('falconservices.big_data.'.config('app.env').'.url_api')
        );

        $this->timeout = config('falconservices.timeout', 30);
    }

    public function login(?Data $data = null): self
    {
        if (!($data instanceof Data)) {
            $data = new Data();
        }

        if ($data->isEmpty() && class_exists(Setting::class)) {
            $data = Setting::query()->byName('datahub_access');
        }

        abort_if(
            $data->isEmpty(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            __('Errors: data is required'),
        );

        abort_if(
            !$data->has('email'),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            __('Errors: email is required'),
        );
        abort_if(
            !$data->has('password'),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            __('Errors: password is required'),
        );

        $response = Http::retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->post("{$this->urlApi}/login", $data->toArray());

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->message = $response->object()->message ?? $this->message;
            $this->errors  = $response->object()->data ?? $this->errors;
            $this->data    = new Data();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = new Data($response->object()->data);

        return $this;
    }

    public function createUser(Data $data): self
    {
        if (!$this->data) {
            $this->data = $data;
        }

        $response = Http::retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->post($this->urlApi, $this->data->toArray());

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->message = $response->object()->message ?? $this->message;
            $this->errors  = $response->object()->data ?? $this->errors;
            $this->data    = new Data();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = new Data($response->object()->data);

        return $this;
    }
}
