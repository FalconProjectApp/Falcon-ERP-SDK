<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Repositories\Shop;

use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class ShopRepository
{
    public bool $success          = false;
    public int $http_code         = 0;
    public string $message        = 'not found';
    public ?string $cnpj          = null;
    public ?string $id            = null;
    public array | object $errors = [];
    public array | object $data   = [];
    private string $urlApi;
    private string $authorization;

    public function __construct(array $params = [])
    {
        $this->urlApi = sprintf(
            '%s/erp/private/shop/shops/v1',
            config('falconservices.shop.' . config('app.env') . '.url_api')
        );

        $this->authorization = $params['authorization'] ?? request()->header('Authorization');
    }

    public function index(): self
    {
        $response = Http::withToken($this->authorization, null)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->get("{$this->urlApi}");

        if (!$response->successful()) {
            $this->data = collect();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }

    public function show(string $id): self
    {
        if (!$this->id) {
            $this->id = $id;
        }

        $response = Http::withToken($this->authorization, null)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->get("{$this->urlApi}/{$this->id}");

        if (!$response->successful()) {
            $this->http_code = $response->status();
            $this->data      = collect();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }

    public function store(Data $data): self
    {
        if (!$this->data) {
            $this->data = $data;
        }

        $response = Http::withToken($this->authorization, null)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->post($this->urlApi, $this->data->toArray());

        if (!$response->successful()) {
            $this->http_code = $response->status();
            $this->data      = collect();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }
}
