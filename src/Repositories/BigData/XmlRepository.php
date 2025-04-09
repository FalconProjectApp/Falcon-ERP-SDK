<?php

namespace FalconERP\Skeleton\Repositories\BigData;

use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class XmlRepository
{
    private string $urlApi;
    private string $authorization;
    public bool $success        = false;
    public int $http_code       = 0;
    public string $message      = 'not found';
    public ?string $cnpj        = null;
    public array|object $errors = [];
    public array|object $data   = [];

    /**
     * Timeout in seconds.
     */
    public int $timeout = 30;

    public function __construct($auth)
    {
        $this->urlApi = sprintf(
            '%s/private/v1/xmls',
            config('services.falcon.big_data.url_api')
        );

        $this->authorization = $auth->data->access_token;
    }

    public function store(Data $data): self
    {
        if (!$this->data) {
            $this->data = $data;
        }

        $response = Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->post($this->urlApi, $this->data);

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

    public function search(string $cnpj)
    {
        if (!$this->cnpj) {
            $this->cnpj = $cnpj;
        }

        $response = Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->get("{$this->urlApi}/{$this->cnpj}/search");

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->message = $response->object()->message ?? $this->message;
            $this->errors  = $response->object()->data ?? $this->errors;
            $this->data    = collect();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }
}
