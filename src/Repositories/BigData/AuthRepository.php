<?php

namespace FalconERP\Skeleton\Repositories\BigData;

use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class AuthRepository
{
    private string $urlApi;
    private string $authorization;
    private string $email;
    private string $secret;
    public bool $success        = false;
    public int $http_code       = 0;
    public string $message      = 'not found';
    public ?string $cnpj        = null;
    public array|object $errors = [];
    public array|object $data   = [];

    public function __construct()
    {
        $this->urlApi = sprintf(
            '%s/auth/v1/login',
            config('services.falcon.big_data.url_api')
        );

        $this->email = config('services.falcon.big_data.email');
        $this->secret = config('services.falcon.big_data.secret');
    }

    public function login(): self
    {
        $response = Http::retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->post($this->urlApi, [
                'email'  => $this->email,
                'password' => $this->secret //TODO: mudar para secret
            ]);

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->message = $response->object()->message ?? $this->message;
            $this->errors  = $response->object()->data ?? $this->errors;
            $this->data    = collect();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = new Data($response->object()->data);

        return $this;
    }
}
