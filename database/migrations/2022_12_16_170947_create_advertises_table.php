<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertises', function (Blueprint $table) {
            $table->id();

            $table->string('url');
            $table->string('identifier');

            $table->string('name');

            $table->string('category')
                ->nullable();
            $table->string('floor')
                ->nullable();
            $table->string('address')
                ->nullable();
            $table->string('area')
                ->nullable();
            $table->string('document_type')
                ->nullable();
            $table->string('repair')
                ->nullable();

            $table->string('longitude')
                ->nullable();

            $table->string('latitude')
                ->nullable();

            $table->text('description');

            $table->json('additional');
            $table->json('phones');

            $table->unique('url');
            $table->unique('identifier');


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
        Schema::dropIfExists('advertises');
    }
};
