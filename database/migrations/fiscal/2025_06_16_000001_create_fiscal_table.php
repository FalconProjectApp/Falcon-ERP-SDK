<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use FalconERP\Skeleton\Enums\Revenue\FiscalDocumentEnum;
use FalconERP\Skeleton\Enums\Fiscal\SerieEnvironmentEnum;
use FalconERP\Skeleton\Enums\Fiscal\NatureOperationTypeEnum;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS fiscal');

        Schema::create('fiscal.nfes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fiscal.imports', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da importação');

            $table->unsignedBigInteger('issuer_people_id')
                ->nullable()
                ->comment('Representa o emitente da importação');

            $table->unsignedBigInteger('recipient_people_id')
                ->nullable()
                ->comment('Representa o emitente da importação');

            $table->string('access_key')
                ->comment('Representa a chave de acesso da importação');

            $table->enum('type', FiscalDocumentEnum::types()->toArray())
                ->comment('Representa o tipo de documento fiscal');

            $table->integer('value')
                ->default(0)
                ->comment('Representa o valor da importação');

            $table->unsignedBigInteger('importer_people_id')
                ->comment('Representa quem realizou a importação');

            $table->enum('status', FiscalDocumentEnum::statuses()->toArray())
                ->default(FiscalDocumentEnum::STATUS_PENDING)
                ->comment('Representa o status da importação');

            $table->timestamp('imported_at')
                ->nullable()
                ->comment('Representa a data de importação');

            $table->timestamp('canceled_at')
                ->nullable()
                ->comment('Representa a data de cancelamento');

            $table->json('data')
                ->nullable()
                ->comment('Representa os dados da importação');

            $table->text('observations')
                ->nullable()
                ->comment('Representa as observações da importação');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fiscal.series', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da série');

            $table->string('description')
                ->comment('Representa a descrição da série');
            $table->string('model')
                ->comment('Representa o modelo da série. Ex: NF-e, NFC-e, etc. tag: mod');
            $table->integer('sequence_number')
                ->default(1)
                ->comment('Representa a sequência da série. Ex: 1, 2, 3, etc.');
            $table->enum('environment', array_map(fn ($case) => $case->value, SerieEnvironmentEnum::cases()))
                ->default('homologation')
                ->comment('Representa o ambiente da série. Ex: produção, homologação');

            $table->unsignedBigInteger('people_issuer_id')
                ->nullable()
                ->comment('Representa o identificador da empresa');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica da série');
        });

        /* Artisan::call('db:seed', [
            '--class' => 'Load_2025_04_20_00001_FiscalSeriesSeeder',
        ]); */

        Schema::create('fiscal.nature_operations', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da natureza de operação');

            $table->string('description')
                ->comment('Representa a descrição da natureza de operação');

            $table->enum('operation_type', array_map(fn ($enum) => $enum->value, NatureOperationTypeEnum::cases()))
                ->default(NatureOperationTypeEnum::TYPE_OTHER->value)
                ->comment('Representa o tipo de operação da nota fiscal');

            $table->foreignId('serie_id')
                ->constrained('fiscal.series')
                ->onDelete('cascade')
                ->comment('Representa o identificador da série');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica da série');
        });

        /* Artisan::call('db:seed', [
            '--class' => 'Load_2025_04_20_00002_FiscalNatureOperationSeeder',
        ]); */

        Schema::create('fiscal.batchs', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do lote');

            $table->string('version_application')
                ->comment('Representa a versão do aplicativo. tag: verAplic');

            $table->string('type_environment')
                ->comment('Representa o tipo de ambiente. tag: tpAmb');

            $table->string('code_status')
                ->comment('Representa o código de status. tag: cStat');

            $table->string('motive_status')
                ->comment('Representa o motivo do status. tag: xMotivo');

            $table->string('unit_federation_code')
                ->comment('Representa a unidade federativa. tag: cUF');

            $table->string('information_receipt_number')
                ->comment('Representa o número do recibo da nota fiscal. tag: inf_rec_nRec');

            $table->dateTime('information_receipt_date')
                ->comment('Representa a data do recibo da nota fiscal. tag: inf_rec_dhRecbto');

            $table->string('average_processing_time')
                ->comment('Representa o tempo médio de processamento. tag: inf_rec_tMed');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica do lote');
        });

        Schema::create('fiscal.protocols', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do protocolo');

            $table->foreignId('batch_id')
                ->constrained('fiscal.batchs')
                ->onDelete('cascade')
                ->comment('Representa o identificador do lote');

            $table->string('type_environment')
                ->comment('Representa o tipo de ambiente. tag: tpAmb');

            $table->string('code_status')
                ->comment('Representa o código de status. tag: cStat');

            $table->dateTime('receipt_date')
                ->comment('Representa a data do recibo da nota fiscal. tag: dhRecbto');

            $table->string('motive_status')
                ->comment('Representa o motivo do status. tag: xMotivo');

            $table->string('number_protocol')
                ->comment('Representa o número do protocolo. tag: nProt');

            $table->json('xml')
                ->comment('Representa o xml do protocolo.');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica da série');
        });

        Schema::create('fiscal.invoices', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da nota fiscal');

            $table->foreignId('batch_id')
                ->constrained('fiscal.batchs')
                ->onDelete('cascade')
                ->comment('Representa o identificador do lote');

            $table->foreignId('nature_operation_id')
                ->constrained('fiscal.nature_operations')
                ->onDelete('cascade')
                ->comment('Representa o identificador da natureza de operação');

            $table->unsignedBigInteger('people_issuer_id')
                ->comment('Representa o identificador do emitente da nota fiscal');

            $table->unsignedBigInteger('people_recipient_id')
                ->comment('Representa o identificador do destinatário da nota fiscal');

            $table->string('type_environment')
                ->comment('Representa o tipo de ambiente. tag: tpAmb');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica da série');
        });

        Schema::create('fiscal.invoice_items', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do item da nota fiscal');

            $table->foreignId('invoice_id')
                ->constrained('fiscal.invoices')
                ->onDelete('cascade')
                ->comment('Representa o identificador da nota fiscal');

            $table->foreignId('stock_id')
                ->constrained('stock.stocks')
                ->onDelete('cascade')
                ->comment('Representa o identificador do estoque');

            $table->integer('quantity')
                ->comment('Representa a quantidade do item');

            $table->integer('unit_value')
                ->comment('Representa o valor unitário do item');

            $table->integer('total_value')
                ->comment('Representa o valor total do item');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica do item da nota fiscal');
        });

        Schema::create('fiscal.invoice_payments', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do pagamento da nota fiscal');

            $table->foreignId('invoice_id')
                ->constrained('fiscal.invoices')
                ->onDelete('cascade')
                ->comment('Representa o identificador da nota fiscal');

            $table->foreignId('payment_method_id')
                ->constrained('finance.payment_methods')
                ->onDelete('cascade')
                ->comment('Representa o identificador do método de pagamento');

            $table->integer('value')
                ->comment('Representa o valor do pagamento');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica do pagamento da nota fiscal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fiscal.invoice_payments');
        Schema::dropIfExists('fiscal.invoice_items');
        Schema::dropIfExists('fiscal.invoices');
        Schema::dropIfExists('fiscal.nature_operations');
        Schema::dropIfExists('fiscal.series');
        Schema::dropIfExists('fiscal.protocols');
        Schema::dropIfExists('fiscal.batchs');
        Schema::dropIfExists('fiscal.imports');
        Schema::dropIfExists('fiscal.nfes');
    }
};
