<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use FalconERP\Skeleton\Enums\People\EmailEnum;
use FalconERP\Skeleton\Enums\People\PeopleContactEnum;
use FalconERP\Skeleton\Enums\People\PeopleDocumentEnum;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS people');

        Schema::create('people.types', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('people.peoples', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name')
                ->nullable();
            $table->text('about')
                ->nullable();
            $table->unsignedbiginteger('types_id');
            $table->foreign('types_id')
                ->references('id')
                ->on('people.types')
                ->onDelete('cascade')
                ->unique();

            $table->boolean('is_public')
                ->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('people.people_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('people_id');
            $table->foreign('people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');
            $table->enum('type', PeopleContactEnum::types()->toArray());
            $table->boolean('main')
                ->default(false);
            $table->string('value');
            $table->timestamps();
            $table->unique(['people_id', 'value'], 'contacts_uk');
        });

        Schema::create('people.people_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('people_id');
            $table->foreign('people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');
            $table->enum('type', PeopleDocumentEnum::types()->toArray());
            $table->string('value')
                ->unique();
            $table->boolean('is_accessible')
                ->default(false);
            $table->timestamps();
        });

        Schema::create('people.addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedbiginteger('people_id');
            $table->foreign('people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade')
                ->unique();

            $table->string('cep')
                ->nullable();

            $table->string('country');
            $table->string('district');
            $table->string('road');
            $table->string('number');
            $table->string('complement')
                ->nullable();
            $table->string('city')
                ->comment('Cidade do endereço')
                ->nullable();
            $table->string('state')
                ->comment('Estado do endereço')
                ->nullable();
            $table->string('city_ibge')
                ->nullable()
                ->comment('Representa o código IBGE da cidade');
            $table->string('state_ibge')
                ->nullable()
                ->comment('Representa o código IBGE do estado');
            $table->integer('main');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('people.companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedbiginteger('peoples_id');
            $table->foreign('peoples_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade')
                ->unique();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('people.emails', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do email');

            $table->unsignedBigInteger('responsible_people_id')
                ->comment('Representa o identificador da pessoa responsável pelo email');
            $table->foreign('responsible_people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');

            $table->string('email_sender')
                ->comment('Representa o email do remetente');
            $table->string('email_receiver')
                ->nullable()
                ->comment('Representa o email do destinatário');

            $table->string('subject')
                ->comment('Representa o assunto do email');

            $table->text('content')
                ->comment('Representa o conteúdo do email');

            $table->enum('status', EmailEnum::statuses()->toArray())
                ->default(EmailEnum::STATUS_DRAFT)
                ->comment('Representa o status do email');

            $table->timestamp('sent_at')
                ->nullable()
                ->comment('Representa a data de envio do email');

            $table->timestamp('delivered_at')
                ->nullable()
                ->comment('Representa a data de entrega do email');

            $table->timestamp('read_at')
                ->nullable()
                ->comment('Representa a data de leitura do email');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('people.notifications', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador da notificação');

            $table->unsignedBigInteger('responsible_people_id')
                ->nullable()
                ->index()
                ->comment('Representa o identificador da pessoa responsável pela notificação, nulo se for geral');
            $table->foreign('responsible_people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');

            $table->morphs('notifiable');

            $table->string('title')
                ->comment('Representa o título da notificação');

            $table->json('content')
                ->comment('Representa o conteúdo da notificação em formato JSON');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('people.notifications_views', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('notification_id')
                ->comment('Representa o identificador da notificação');
            $table->foreign('notification_id')
                ->references('id')
                ->on('people.notifications')
                ->onDelete('cascade');

            $table->unsignedBigInteger('viewer_people_id')
                ->comment('Representa o identificador da pessoa que visualizou a notificação');
            $table->foreign('viewer_people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');

            $table->dateTime('viewed_at')
                ->comment('Representa a data e hora que a notificação foi visualizada');

            $table->timestamps();
        });

        Schema::create('people.people_follows', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do follow');

            $table->unsignedBigInteger('follower_people_id')
                ->comment('Representa o identificador da pessoa que está seguindo');
            $table->foreign('follower_people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade');

            $table->morphs('followable');

            $table->timestamps();
        });

        Schema::create('people.people_segments', function (Blueprint $table) {
            $table->id()
                ->comment('Representa o identificador do segmento');

            $table->foreignId('people_id');
            $table->foreign('people_id')
                ->references('id')
                ->on('people.peoples')
                ->onDelete('cascade')
                ->comment('Representa o identificador da pessoa');

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
        Schema::dropIfExists('people.people_segments');
        Schema::dropIfExists('people.people_follows');
        Schema::dropIfExists('people.notifications_views');
        Schema::dropIfExists('people.notifications');
        Schema::dropIfExists('people.emails');
        Schema::dropIfExists('people.addresses');
        Schema::dropIfExists('people.companies');
        Schema::dropIfExists('people.people_documents');
        Schema::dropIfExists('people.people_contacts');
        Schema::dropIfExists('people.people');
        Schema::dropIfExists('people.types');
    }
};
