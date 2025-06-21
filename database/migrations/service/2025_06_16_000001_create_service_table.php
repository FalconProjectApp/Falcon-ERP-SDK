<?php

declare(strict_types = 1);

use FalconERP\Skeleton\Enums\Service\OrderEnum;
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
        DB::statement('CREATE SCHEMA IF NOT EXISTS service');

        Schema::create('service.services', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('value')
                ->default(0);
            $table->time('service_time')
                ->default('00:00:00');
            $table->boolean('active')
                ->default(1);
            $table->text('observations')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service.orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('responsible_id');
            $table->unsignedBigInteger('taker_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('scheduled_at')
                ->nullable()
                ->after('status');
            $table->text('obs')
                ->nullable();
            $table->enum('status', OrderEnum::statuses()->toArray())
                ->default(OrderEnum::STATUS_OPEN);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service.order_bodies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')
                ->references('id')
                ->on('service.orders')
                ->onDelete('cascade');
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('service.services')
                ->onDelete('cascade');
            $table->string('worked_at')
                ->default('00:00:00');
            $table->timestamp('started_at')
                ->nullable();
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
        Schema::dropIfExists('service.order_bodies');
        Schema::dropIfExists('service.orders');
        Schema::dropIfExists('service.services');
    }
};
