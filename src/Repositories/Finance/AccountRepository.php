<?php

namespace FalconERP\Skeleton\Repositories\Finance;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class AccountRepository
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

    public int $timeout = 30;

    public function __construct()
    {
        $this->urlApi = sprintf(
            '%s/erp/private/financial-accounts/v1',
            config('falconservices.finance.'.config('app.env').'.url_api')
        );

        $this->authorization = request()->header('Authorization');

        $this->timeout = config('falconservices.timeout', 30);
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
            ->get($this->urlApi);

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

    public function show(?string $id): self
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
        $this->data    = $response->object()->data;

        return $this;
    }

    public function store(
        ?string $people_id,
        ?string $due_date = null,
        ?string $issue_date = null,
        ?string $description = null,
        ?string $obs = null,
        bool $is_value_installment = false,
        ?string $type = 'pay',
        int $fees = 0,
        int $discount = 0,
        int $fine = 0,
        int $value = 0,
        string $repetition = 'not_recurrent',
        int $installment_start = 0,
        int $installments_amount = 1,
        ?array $data = [],
    ): self {
        $data = new Data($data);

        $data->description          = $description;
        $data->people_id            = $people_id;
        $data->type                 = $type;
        $data->fees                 = $fees;
        $data->discount             = $discount;
        $data->fine                 = $fine;
        $data->obs                  = $obs ?? $data->description;
        $data->value                = $value;
        $data->is_value_installment = $is_value_installment;
        $data->due_date             = $due_date ?? Carbon::now()->format('Y-m-d h:m:s');
        $data->issue_date           = $issue_date ?? Carbon::now()->format('Y-m-d h:m:s');
        $data->repetition           = $repetition;
        $data->installment_start    = $installment_start;
        $data->installments_amount  = $installments_amount;

        if (blank($this->authorization)) {
            return $this;
        }

        $response = Http::withToken($this->authorization, null)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->post($this->urlApi, $data->toArray());

        $this->http_code = $response->status();

        if (!$response->successful()) {
            $this->errors = $response->object()->data ?? [];
            $this->data   = collect();

            return $this;
        }

        $this->success = $response->successful() && $response->object()->success;
        $this->message = $response->object()->message;
        $this->data    = $response->object()->data;

        return $this;
    }
}
