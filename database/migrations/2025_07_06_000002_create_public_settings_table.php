<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('people.settings');

        Schema::create('settings', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da configuração');
            $table->string('name')
                ->comment('Representa o nome da configuração');
            $table->json('value')
                ->comment('Representa o valor da configuração');
            $table->string('description')
                ->nullable()
                ->comment('Representa a descrição da configuração');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
