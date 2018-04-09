<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJiraWorklogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jira_worklogs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('self');
            $table->json('author');
            $table->json('updateAuthor');
            $table->text('comment');
            $table->dateTime('created');
            $table->dateTime('updated');
            $table->dateTime('started');
            $table->string('timeSpent');
            $table->unsignedInteger('timeSpentSeconds');
            $table->unsignedInteger('jira_id');

            $table->unsignedInteger('jira_issue_id');
            $table->foreign('jira_issue_id')
              ->references('jira_id')
              ->on('jira_issues')
              ->onDelete('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade');

            $table->index(['jira_id', 'jira_issue_id']);

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
        Schema::dropIfExists('jira_worklogs');
    }
}
