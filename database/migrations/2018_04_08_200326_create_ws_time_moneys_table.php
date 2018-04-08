<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsTimeMoneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_time_moneys', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('ws_id')->index();

            $table->unsignedInteger('ws_task_id');
            $table->foreign('ws_task_id')
              ->references('ws_id')
              ->on('ws_tasks')
              ->onDelete('cascade');

            $table->json('jsonTask');
            $table->string('comment');
            $table->time('time');
            $table->float('money');
            $table->date('date');
            $table->boolean('is_timer');
            $table->json('user_from');

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
        Schema::dropIfExists('ws_time_moneys');
    }
}
