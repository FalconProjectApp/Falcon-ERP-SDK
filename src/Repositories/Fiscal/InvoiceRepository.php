<?php

namespace FalconERP\Skeleton\Repositories\Fiscal;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class InvoiceRepository
{
    private string $urlApi;
    private ?string $authorization;
    public bool $success        = false;
    public int $http_code       = 0;
    public string $message      = 'not found';
    public ?string $cnpj        = null;
    public ?string $id          = null;
    public array|object $errors = [];
    public array|object $data   = [];

    public int $timeout = config('falconservices.timeout', 30);

    public function __construct()
    {
        $this->urlApi = sprintf(
            '%s/erp/private/invoices/v1',
            config('falconservices.fiscal.'.config('app.env').'.url_api')
        );

        $this->authorization = request()->header('Authorization');
    }

    public function index(): self
    {
        if (blank($this->authorization)) {
            return $this;
        }

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
}
