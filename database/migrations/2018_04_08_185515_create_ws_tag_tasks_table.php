<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsTagTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_tag_tasks', function (Blueprint $table) {
            $table->unsignedInteger('ws_task_id');
            $table->foreign('ws_task_id')
              ->references('id')
              ->on('ws_tasks')
              ->onDelete('cascade');

            $table->unsignedInteger('ws_tag_id');
            $table->foreign('ws_tag_id')
              ->references('id')
              ->on('ws_tags')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ws_tag_tasks');
    }
}
