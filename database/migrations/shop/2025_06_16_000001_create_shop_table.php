<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use FalconERP\Skeleton\Enums\RequestEnum;
use Illuminate\Database\Schema\Blueprint;
use FalconERP\Skeleton\Enums\Shop\ShopEnum;
use Illuminate\Database\Migrations\Migration;
use FalconERP\Skeleton\Enums\Stock\Driver\DriverStatusEnum;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS shop');

        Schema::create('shop.shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responsible_people_id')
                ->nullable()
                ->comment('Representa o identificador do responsável pela loja');
            $table->foreignId('issuer_people_id')
                ->nullable()
                ->comment('Representa o identificador do emissor da loja');
            $table->string('name');
            $table->string('slug')
                ->nullable()
                ->comment('Representa o slug da loja');
            $table->text('obs')
                ->nullable();
            $table->enum('type', ShopEnum::types()->toArray())
                ->default(ShopEnum::TYPES_SERVICE);
            $table->json('authorization')
                ->nullable();
            $table->json('metadata')
                ->nullable();
            $table->enum('status', ShopEnum::statuses()->toArray())
                ->default(ShopEnum::STATUS_CLOSEDS);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop.shop_linkeds', function (Blueprint $table) {
            $table->id();
            $table->morphs('linkable');
            $table->unsignedBigInteger('shop_id');

            $table->foreign('shop_id')
                ->references('id')
                ->on('shop.shops')
                ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('shop.shop_segments', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do segmento');

            $table->foreignId('shop_id');
            $table->foreign('shop_id')
                ->references('id')
                ->on('shop.shops')
                ->onDelete('cascade')
                ->comment('Representa o identificador da loja');

            $table->string('name')
                ->comment('Representa o nome do segmento');
            $table->string('value')
                ->comment('Representa o valor do segmento');

            $table->timestamps();
            $table->softDeletes()
                ->comment('Representa a data de exclusão lógica do segmento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop.shop_segments');
        Schema::dropIfExists('shop.shop_linkeds');
        Schema::dropIfExists('shop.shops');
    }
};
