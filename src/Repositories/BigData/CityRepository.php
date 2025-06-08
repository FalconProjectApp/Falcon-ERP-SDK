<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Repositories\BigData;

use Illuminate\Support\Facades\Http;

class CityRepository
{
    public bool $success          = false;
    public int $http_code         = 0;
    public string $message        = 'not found';
    public ?string $cnpj          = null;
    public array | object $errors = [];
    public array | object $data   = [];

    /**
     * Timeout in seconds.
     */
    public int $timeout = config('falconservices.timeout', 30);
    private string $urlApi;
    private ?string $authorization;

    public function __construct($auth)
    {
        $this->urlApi = sprintf(
            '%s/private/v1/cities',
            config('falconservices.big_data.'.config('app.env').'.url_api')
        );

        $this->authorization = $auth->data->access_token;
    }

    public function get(?string $search = null)
    {
        if (blank($this->authorization)) {
            return $this;
        }

        $response = Http::withToken($this->authorization)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->timeout)
            ->get($this->urlApi, [
                'search' => $search,
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
        $this->data    = collect($response->object()->data);

        return $this;
    }
}
