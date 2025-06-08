<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Repositories\BigData;

use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class IpRepository
{
    public bool $success        = false;
    public int $http_code       = 0;
    public string $message      = 'not found';
    public ?string $ip          = null;
    public array|object $errors = [];
    public array|object $data   = [];

    /**
     * Timeout in seconds.
     */
    public int $timeout = 30;
    private string $urlApi;
    private ?string $authorization;

    public function __construct($auth)
    {
        $this->urlApi = sprintf(
            '%s/private/v1/ip',
            config('services.falcon.big_data.url_api')
        );

        $this->authorization = $auth->data->access_token;

        $this->timeout = config('falconservices.timeout', 30);
    }

    public function store(Data $data): self
    {
        if (!$this->data) {
            $this->data = $data;
        }

        if (blank($this->authorization)) {
            return $this;
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

    public function search(string $ip)
    {
        if (!$this->ip) {
            $this->ip = $ip;
        }

        if (blank($this->authorization)) {
            return $this;
        }

        $response = Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->get("{$this->urlApi}/{$this->ip}/search");

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
