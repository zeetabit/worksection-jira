<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_projects', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('ws_id')->index();

            $table->string('name');
            $table->string('page');
            $table->string('status');
            $table->string('company');
            $table->json('user_from');
            $table->json('user_to');

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
        Schema::dropIfExists('ws_projects');
    }
}
