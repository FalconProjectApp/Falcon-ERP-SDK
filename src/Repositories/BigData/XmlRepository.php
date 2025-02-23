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

    public function __construct()
    {
        $this->urlApi = sprintf(
            '%s/private/v1/xmls',
            config('services.falcon.big_data.url_api')
        );

        $this->authorization = request()->header('Authorization');
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

        $response = Http::withToken($this->authorization, null)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
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

    public function auth()
    {
        echo "<a href='https://auth.mercadolivre.com.br/authorization?response_type=code&client_id=3543217050675060&redirect_uri={$this->redirect_uri}'>teste</a>";

        $result = json_decode(Http::withHeaders([
            'accept'       => 'application/json',
            'content-type' => 'application/x-www-form-urlencoded',
        ])->post("{$this->urlApi}/oauth/token", [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'code'          => request()->code,
            'redirect_uri'  => $this->redirect_uri,
        ])->getBody()->getContents());

        if (isset($result->error)) {
            dd($result, $this);
        }
        $this->access_token  = $result->access_token;
        $this->refresh_token = $result->refresh_token;
        dd($result, $this);
    }

    public function getUser()
    {
        return Http::withHeaders([
            'accept'        => 'application/json',
            'Authorization' => "Bearer {$this->access_token}",
        ])->get("{$this->urlApi}/users/me")->getBody()->getContents();
    }
}
