<?php

declare(strict_types = 1);

use FalconERP\Skeleton\Enums\Finance\BillEnum;
use FalconERP\Skeleton\Enums\Finance\FinancialAccountEnum;
use FalconERP\Skeleton\Enums\Finance\PaymentEnum;
use FalconERP\Skeleton\Enums\ReleaseTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS finance');

        Schema::create('finance.portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('description');

            $table->unsignedbiginteger('people_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.actions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedbiginteger('portfolio_id');
            $table->foreign('portfolio_id')
                ->references('id')
                ->on('finance.portfolios')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.action_movements', function (Blueprint $table) {
            $table->id();
            $table->string('description');

            $table->unsignedbiginteger('action_id');
            $table->foreign('action_id')
                ->references('id')
                ->on('finance.actions')
                ->onDelete('cascade');

            $table->enum('types', ['compra', 'venda'])
                ->comment('Compra | Venda');

            $table->string('trading note')
                ->nullable();
            $table->date('payday')
                ->nullable();
            $table->integer('amount');
            $table->decimal('value', 25, 10);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.dividend_events', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
        });

        DB::table('finance.dividend_events')->insert([
            [
                'id'          => 0,
                'description' => 'Juros sobre capital próprio',
                'created_at'  => now()->format('Y-m-d H:i:s'),
                'updated_at'  => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id'          => 1,
                'description' => 'Leilões de frações',
                'created_at'  => now()->format('Y-m-d H:i:s'),
                'updated_at'  => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id'          => 2,
                'description' => 'Dividendo',
                'created_at'  => now()->format('Y-m-d H:i:s'),
                'updated_at'  => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id'          => 3,
                'description' => 'Rendimento',
                'created_at'  => now()->format('Y-m-d H:i:s'),
                'updated_at'  => now()->format('Y-m-d H:i:s'),
            ],
        ]);

        Schema::create('finance.action_dividends', function (Blueprint $table) {
            $table->id();
            $table->string('description');

            $table->unsignedbiginteger('action_id');
            $table->foreign('action_id')
                ->references('id')
                ->on('finance.actions')
                ->onDelete('cascade');

            $table->unsignedbiginteger('dividend_event_id');
            $table->foreign('dividend_event_id')
                ->references('id')
                ->on('finance.dividend_events')
                ->onDelete('cascade');

            $table->date('payment_forecast');
            $table->date('approval_date')
                ->nullable();
            $table->integer('amount');
            $table->decimal('value', 25, 10);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('description');

            $table->unsignedbiginteger('people_id');

            $table->enum('type', ReleaseTypeEnum::types()->toArray())
                ->default('client');

            $table->enum('status', FinancialAccountEnum::statuses()->toArray())
                ->after('people_id')
                ->default(FinancialAccountEnum::STATUS_OPENED);

            $table->boolean('active')
                ->default(1);

            $table->text('obs')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.releases_types', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('release_type', ['input', 'output']);
            $table->enum('type', ['system', 'client']);
            $table->boolean('active')
                ->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.financial_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedbiginteger('financial_accounts_id');
            $table->foreign('financial_accounts_id')
                ->references('id')
                ->on('finance.financial_accounts')
                ->onDelete('cascade');

            $table->unsignedbiginteger('releases_types_id');
            $table->foreign('releases_types_id')
                ->references('id')
                ->on('finance.releases_types')
                ->onDelete('cascade');

            $table->text('obs')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.bills', function (Blueprint $table) {
            $table->id();
            $table->string('description')
                ->comment('Descrição da fatura');

            $table->unsignedbiginteger('people_id')
                ->comment('Pessoa da fatura');
            $table->foreign('people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');

            $table->unsignedbiginteger('financial_account_id')
                ->nullable()
                ->comment('Conta financeira padrão');
            $table->foreign('financial_account_id')
                ->references('id')
                ->on('finance.financial_accounts')
                ->onDelete('cascade');

            $table->enum('type', BillEnum::types()->toArray())
                ->default(BillEnum::TYPE_RECEIVE)
                ->comment('Tipo de fatura');

            $table->enum('repetition', BillEnum::repetitions()->toArray())
                ->default(BillEnum::REPETITION_NOT_RECURRENT)
                ->comment('Repetição da fatura');

            $table->enum('periodicity', BillEnum::periodicities()->toArray())
                ->default(BillEnum::PERIODICITY_MONTHLY)
                ->comment('Periodicidade da fatura');

            $table->enum('status', BillEnum::statuses()->toArray())
                ->default(BillEnum::STATUS_OPEN)
                ->comment('Status da fatura');

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained('finance.payment_methods')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Representa o método de pagamento da conta');

            $table->integer('fees')
                ->default(1)
                ->comment('Juros por atraso em %');

            $table->integer('discount')
                ->default(0)
                ->comment('Desconto em %');

            $table->integer('fine')
                ->default(0)
                ->comment('Multa em %');

            $table->text('obs')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.bill_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedbiginteger('bill_id')
                ->comment('Parcela da fatura');
            $table->foreign('bill_id')
                ->references('id')
                ->on('finance.bills')
                ->onDelete('cascade');

            $table->date('due_date')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('Data de vencimento');
            $table->date('issue_date')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('Data de emissão');

            $table->integer('value')
                ->default(0)
                ->comment('Valor da parcela');
            $table->integer('value_paid')
                ->default(0)
                ->comment('Valor pago');
            $table->integer('value_interest')
                ->default(0)
                ->comment('Juros');

            $table->enum('status', BillEnum::statuses()->toArray())
                ->default(BillEnum::STATUS_OPEN)
                ->comment('Status da parcela');

            $table->text('obs')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('finance.payment_methods', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do método de pagamento');

            $table->string('description')
                ->comment('Representa a descrição do método de pagamento');

            $table->text('observations')
                ->nullable()
                ->comment('Representa as observações do método de pagamento');

            $table->enum('method', PaymentEnum::methods()->toArray())
                ->default(PaymentEnum::METHOD_A_VISTA)
                ->comment('Representa o método de pagamento');

            $table->enum('flag', PaymentEnum::flags()->toArray())
                ->nullable()
                ->comment('Representa a bandeira do método de pagamento (se houver)');

            $table->enum('type', PaymentEnum::types()->toArray());

            $table->enum('status', PaymentEnum::statuses()->toArray())
                ->default(PaymentEnum::STATUS_ACTIVE)
                ->comment('Representa o status do método de pagamento');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finance.payment_methods');
        Schema::dropIfExists('finance.bill_installments');
        Schema::dropIfExists('finance.bills');
        Schema::dropIfExists('finance.financial_movements');
        Schema::dropIfExists('finance.releases_types');
        Schema::dropIfExists('finance.financial_accounts');
        Schema::dropIfExists('finance.action_dividends');
        Schema::dropIfExists('finance.dividend_events');
        Schema::dropIfExists('finance.action_movements');
        Schema::dropIfExists('finance.actions');
        Schema::dropIfExists('finance.portfolios');

    }
};
