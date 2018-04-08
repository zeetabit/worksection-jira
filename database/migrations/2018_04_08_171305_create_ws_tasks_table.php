<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_tasks', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('ws_id')->index();

            $table->string('name');
            $table->string('page');
            $table->string('status');
            $table->unsignedTinyInteger('priority');
            $table->json('user_from');
            $table->json('user_to');
            $table->dateTime('date_added');
            $table->dateTime('date_closed')->nullable();
            //tags [0] = "Docs"
            $table->date('date_start')->nullable();
            $table->date('date_end');
            $table->unsignedInteger('max_time')->default(0);

            $table->unsignedInteger('ws_project_id');
            $table->foreign('ws_project_id')
              ->references('ws_id')
              ->on('ws_projects')
              ->onDelete('cascade');

            $table->timestamps();
        });

        Schema::table('ws_tasks', function (Blueprint $table) {
            $table->unsignedInteger('ws_parent_task_id')->nullable();
            $table->foreign('ws_parent_task_id')
              ->references('ws_id')
              ->on('ws_tasks')
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
        Schema::dropIfExists('ws_tasks');
    }
}
