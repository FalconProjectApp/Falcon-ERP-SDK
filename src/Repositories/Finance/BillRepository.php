<?php

namespace FalconERP\Skeleton\Repositories\Finance;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use QuantumTecnology\ValidateTrait\Data;

class BillRepository
{
    private string $urlApi;
    private string $authorization;
    public bool $success        = false;
    public int $http_code       = 0;
    public string $message      = 'not found';
    public ?string $cnpj        = null;
    public ?string $id          = null;
    public array|object $errors = [];
    public array|object $data   = [];

    public function __construct()
    {
        $this->urlApi        = config('services.falcon.finance.url_api');
        $this->authorization = request()->header('Authorization');
    }

    public function index(): self
    {
        $response = Http::withToken($this->authorization, null)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->asJson()
            ->get("{$this->urlApi}/erp/private/bills/v1");

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
            ->get("{$this->urlApi}/erp/private/bills/v1/{$this->id}");

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

    public function store(
        ?string $people_id = null,
        ?string $due_date = null,
        ?string $issue_date = null,
        ?string $description = null,
        ?string $obs = null,
        ?string $financial_account_id = null,
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
        $data->people_id            = $people_id ?? $data->people_id;
        $data->financial_account_id = $financial_account_id;
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

        abort_if(
            null === $data->people_id,
            Response::HTTP_BAD_REQUEST,
            'Pessoa não informada.'
        );

        $this->urlApi = "{$this->urlApi}/erp/private/bills/v1";

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
        $this->data    = collect($response->object()->data);

        return $this;
    }
}
