<?php

use Illuminate\Support\Facades\Schema;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->morphs('archivable');
            $table->enum('name', ArchiveEnum::name()->toArray())
                ->index();
            $table->boolean('main')
                ->default(false)
                ->index();
            $table->boolean('active')
                ->default(true)
                ->index();
            $table->integer('order')
                ->default(0)
                ->index();
            $table->string('key');
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
        Schema::dropIfExists('archives');
    }
};
