<?php

declare(strict_types = 1);

use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Enums\Stock\Shipment\ShipmentStatusEnum;
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
        if (in_array(
            config('database.connections.' . config('database.default') . '.driver'),
            ['pgsql']
        )) {
            DB::statement('CREATE SCHEMA IF NOT EXISTS stock');
        }

        Schema::create('stock.groups', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.volume_types', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('initials');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.products', function (Blueprint $table) {
            $table->id();

            // Grupo
            $table->unsignedbiginteger('group_id');
            $table->foreign('group_id')
                ->references('id')
                ->on('stock.groups')
                ->onDelete('cascade');

            // Unidade, caixac12
            $table->unsignedbiginteger('volume_type_id');
            $table->foreign('volume_type_id')
                ->references('id')
                ->on('stock.volume_types')
                ->onDelete('cascade');

            $table->boolean('status')
                ->default(false);
            $table->string('description');

            $table->string('ncm')
                ->nullable()
                ->comment('Representa o código NCM do produto');

            $table->string('bar_code')
                ->nullable();
            $table->string('provider_code')
                ->nullable();
            $table->text('observations')
                ->nullable();
            $table->integer('last_buy_value')
                ->default(0);
            $table->integer('last_sell_value')
                ->default(0);
            $table->integer('last_rent_value')
                ->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.product_segments', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do segmento');

            $table->foreignId('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('stock.products')
                ->onDelete('cascade')
                ->comment('Representa o identificador do produto');

            $table->string('name')
                ->comment('Representa o nome do segmento');
            $table->string('value')
                ->comment('Representa o valor do segmento');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica do segmento');
        });

        Schema::create('stock.product_comments', function (Blueprint $table) {
            $table->id();

            $table->unsignedbiginteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('stock.products')
                ->onDelete('cascade');

            $table->unsignedbiginteger('product_comment_id')
                ->nullable();
            $table->foreign('product_comment_id')
                ->references('id')
                ->on('stock.product_comments')
                ->onDelete('cascade');

            $table->unsignedbiginteger('people_id')
                ->nullable();

            $table->text('comment')
                ->nullable();

            $table->string('origin')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.stocks', function (Blueprint $table) {
            $table->id();

            $table->string('description')
                ->comment('Representa a descrição do estoque')
                ->nullable();

            $table->unsignedbiginteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('stock.products')
                ->onDelete('cascade');

            $table->string('color')
                ->nullable();
            $table->boolean('on_shop')
                ->default(0);
            $table->string('measure')
                ->nullable();
            $table->string('weight')
                ->nullable();
            $table->string('height')
                ->nullable();
            $table->string('width')
                ->nullable();
            $table->string('depth')
                ->nullable();

            $table->integer('balance_transit')
                ->default(0);
            $table->integer('balance_stock')
                ->default(0);
            $table->string('value')
                ->default(0);
            $table->text('observations')
                ->nullable();
            $table->boolean('status')
                ->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.request_types', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('request_type', RequestEnum::requestTypes()->toArray());
            $table->enum('type', RequestEnum::types()->toArray());
            $table->boolean('is_active')
                ->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.requests', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da requisição');

            $table->string('description')
                ->comment('Representa a descrição da requisição')
                ->nullable();

            $table->unsignedBigInteger('request_type_id')
                ->comment('Representa o tipo da requisição');
            $table->foreign('request_type_id')
                ->references('id')
                ->on('stock.request_types')
                ->onDelete('cascade');

            $table->unsignedBigInteger('payment_method_id')
                ->nullable()
                ->comment('Representa o método de pagamento da requisição');

            $table->unsignedbiginteger('responsible_id')
                ->comment('Representa o responsável pela requisição (usuário)');

            $table->unsignedbiginteger('third_id')
                ->nullable()
                ->comment('Representa o terceiro da requisição (fornecedor, cliente, etc)');

            $table->unsignedbiginteger('allower_id')
                ->nullable();

            $table->string('discount_value')
                ->default(0)
                ->comment('Representa o valor do desconto aplicado na requisição');
            $table->string('freight_value')
                ->default(0)
                ->comment('Representa o valor do frete aplicado na requisição');

            $table->text('observations')
                ->nullable()
                ->comment('Representa as observações da requisição');
            $table->enum('status', RequestEnum::requestStatus()->toArray())
                ->default(RequestEnum::REQUEST_STATUS_OPEN)
                ->comment('Representa o status da requisição');
            $table->integer('type')
                ->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.shipments', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do carregamento');

            $table->unsignedBigInteger('driver_id')
                ->nullable()
                ->comment('Representa o identificador do motorista responsável pelo carregamento');

            $table->string('status')
                ->default(ShipmentStatusEnum::PENDING)
                ->comment('Representa o status do carregamento');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.shipment_routes', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da rota do carregamento');

            $table->unsignedBigInteger('shipment_id')
                ->comment('Representa o identificador do carregamento');

            $table->foreign('shipment_id')
                ->references('id')
                ->on('stock.shipments')
                ->onDelete('cascade');

            $table->string('route')
                ->comment('Representa a rota do carregamento, armazenada como um polígono geográfico');

            $table->integer('priority')
                ->default(0)
                ->comment('Representa a prioridade da rota, onde 0 é a mais alta prioridade');

            $table->dateTime('departured_at')
                ->nullable()
                ->comment('Representa a data e hora de partida do carregamento');
            $table->dateTime('arrived_at')
                ->nullable()
                ->comment('Representa a data e hora de chegada do carregamento');
            $table->dateTime('estimated_arrival_at')
                ->nullable()
                ->comment('Representa a data e hora estimada de chegada do carregamento');
            $table->dateTime('estimated_departure_at')
                ->nullable()
                ->comment('Representa a data e hora estimada de partida do carregamento');

            $table->string('status')
                ->default(ShipmentStatusEnum::PENDING)
                ->comment('Representa o status da rota do carregamento');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock.items', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do item da requisição');

            $table->unsignedbiginteger('request_id')
                ->comment('Representa o identificador da requisição');
            $table->foreign('request_id')
                ->references('id')
                ->on('stock.requests')
                ->onDelete('cascade');

            $table->unsignedbiginteger('stock_id')
                ->comment('Representa o estoque do item da requisição');
            $table->foreign('stock_id')
                ->references('id')
                ->on('stock.stocks')
                ->onDelete('cascade');

            $table->unsignedbiginteger('shipment_id')
                ->nullable()
                ->comment('Representa o identificador do carregamento associado ao item da requisição');
            $table->foreign('shipment_id')
                ->references('id')
                ->on('stock.shipments')
                ->onDelete('cascade');

            $table->integer('value')
                ->default(0);
            $table->integer('discount')
                ->default(0);
            $table->bigInteger('amount')
                ->default(0);

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
        Schema::dropIfExists('stock.stocks');
        Schema::dropIfExists('stock.product_comments');
        Schema::dropIfExists('stock.product_segments');
        Schema::dropIfExists('stock.products');
        Schema::dropIfExists('stock.items');
        Schema::dropIfExists('stock.requests');
        Schema::dropIfExists('stock.request_types');
        Schema::dropIfExists('stock.volume_types');
        Schema::dropIfExists('stock.groups');
        Schema::dropIfExists('stock.shipment_routes');
        Schema::dropIfExists('stock.shipments');
    }
};
