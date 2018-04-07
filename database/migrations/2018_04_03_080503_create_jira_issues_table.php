<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJiraIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jira_issues', function (Blueprint $table) {
            $table->increments('id');

            $table->string('expand');
            $table->unsignedInteger('jira_id')->index();
            $table->string('key')->index();
            $table->json('issuetype');
            $table->json('components');
            $table->unsignedInteger('timespent')->nullable();
            $table->unsignedInteger('timeoriginalestimate')->nullable();
            $table->string('description');

            $table->unsignedInteger('jira_project_id');
            $table->foreign('jira_project_id')
              ->references('jira_id')
              ->on('jira_projects')
              ->onDelete('cascade');

            $table->index(['jira_id', 'jira_project_id']);

            $table->json('fixVersions');
            $table->unsignedInteger('aggregatetimespent')->nullable();
            $table->json('resolution');
            $table->unsignedInteger('aggregatetimeestimate')->nullable();
            $table->dateTime('resolutiondate')->nullable();
            $table->integer('workratio');
            $table->string('summary');
            $table->dateTime('lastViewed');
            $table->json('watches');
            $table->json('creator');
            $table->json('subtasks');
            $table->dateTime('created');
            $table->json('reporter');
            $table->json('aggregateprogress');
            $table->json('priority');
            $table->json('labels');
            $table->unsignedInteger('timeestimate')->nullable();
            $table->unsignedInteger('aggregatetimeoriginalestimate')->nullable();
            $table->json('versions');
            $table->dateTime('duedate')->nullable();
            $table->json('progress');
            $table->json('issuelinks');
            $table->json('votes');
            $table->json('assignee');
            $table->dateTime('updated');
            $table->json('status');

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
        Schema::dropIfExists('jira_issues');
    }
}
