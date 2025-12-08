<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Repositories\Shop;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
    public array | object $params = [];

    public int $timeout = 30;
    private string $urlApi;
    private ?string $authorization;

    public function __construct(array $params = [])
    {
        $this->urlApi = sprintf(
            '%s/erp/private/shops/v1',
            config('falconservices.shop.' . config('app.env') . '.url_api')
        );

        $this->authorization = Cache::get(sprintf('%s_user_%s', tenant()->base, auth()->user()->id)) ?? $params['authorization'] ?? request()->header('Authorization');

        $this->timeout = config('falconservices.timeout', 30);
    }

    public function index(?string $search = null, ?int $perPage = null): self
    {
        if (blank($this->authorization)) {
            return $this;
        }

        if (!blank($search)) {
            $this->params['search'] = $search;
        }

        if (!blank($perPage)) {
            $this->params['per_page'] = $perPage;
        }

        $response = Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->get($this->urlApi, $this->params);

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->message = $response->object()->message ?? $this->message;
            $this->errors  = $response->object()->data ?? $this->errors;
            $this->data    = collect();

            Log::error('Request failed', [
                'http_code' => $this->http_code,
                'response'  => $response->body(),
            ]);

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }

    public function indexAsync(?string $search = null, ?int $perPage = null): self | PromiseInterface
    {
        if (blank($this->authorization)) {
            return $this;
        }

        if (!blank($search)) {
            $this->params['search'] = $search;
        }

        if (!blank($perPage)) {
            $this->params['per_page'] = $perPage;
        }

        return Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->async()
            ->get($this->urlApi, $this->params)
            ->then(function ($response) {
                $this->http_code = $response->status();

                if (!$response->successful()) {
                    $this->message = $response->object()->message ?? $this->message;
                    $this->errors  = $response->object()->data ?? $this->errors;
                    $this->data    = collect();

                    Log::error('Request failed', [
                        'http_code' => $this->http_code,
                        'response'  => $response->body(),
                    ]);

                    return $this;
                }

                $this->success = $response->successful() && $response->object()->success;
                $this->message = $response->object()->message;
                $this->data    = collect($response->object()->data);

                return $this;
            });
    }

    public function show(string $id): self
    {
        if (!$this->id) {
            $this->id = $id;
        }

        if (blank($this->authorization)) {
            return $this;
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

        if (blank($this->authorization)) {
            return $this;
        }

        $response = Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->post($this->urlApi, $this->data->toArray());

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->message = $response->object()->message ?? $this->message;
            $this->errors  = $response->object()->data ?? $this->errors;
            $this->data    = collect();

            Log::error('Request failed', [
                'http_code' => $this->http_code,
                'response'  => $response->body(),
            ]);

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }
}
