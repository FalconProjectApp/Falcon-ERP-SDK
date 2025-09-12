<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Repositories\BigData;

use FalconERP\Skeleton\Falcon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    public int $timeout = 30;
    private string $urlApi;
    private ?string $authorization;

    public function __construct($auth)
    {
        $this->urlApi = sprintf(
            '%s/private/v1/cities',
            config('falconservices.big_data.'.config('app.env').'.url_api')
        );

        $this->authorization();

        $this->timeout = config('falconservices.timeout', 30);
    }

    public function authorization(): ?string
    {
        $cacheKey = sprintf('%s_falcon_big_data_auth', database()->base);

        if (!Cache::has($cacheKey)) {
            $auth = Falcon::bigDataService('auth')->login();

            if (isset($auth->data->access_token, $auth->data->expires_in)) {
                Cache::put(
                    $cacheKey,
                    $auth->data->access_token,
                    now()->addMinutes(max(1, $auth->data->expires_in / 60 - 1))
                );
            } else {
                throw new \RuntimeException('Failed to retrieve authorization token.');
            }
        }

        return $this->authorization = Cache::get($cacheKey);
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

            Log::warning('Request failed', (array) $response);

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = collect($response->object()->data);

        return $this;
    }
}
