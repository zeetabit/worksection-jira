<?php

namespace App\Console\Commands;

use App\Models\Jira\Issue;
use App\Models\Jira\Project;
use App\Models\Jira\Session;
use App\Models\Jira\Worklog;
use App\Models\Ws\Task;
use App\Models\Ws\TimeMoney;
use App\Services\Api\WorkSectionService;
use App\User;
use Atlassian\JiraRest\Facades\Jira;
use Atlassian\JiraRest\Helpers\Projects;
use Carbon\Carbon;
use Illuminate\Console\Command;

class jira2WsTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:jira2ws';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push JIRA2WS tasks.';

    /** @var WorkSectionService $wsService */
    protected $wsService;

    /** @var syncWSTasksCommand $syncCommand */
    protected $syncCommand;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->syncCommand = $syncCommand = new syncWSTasksCommand();
        $this->wsService = $wsService = new WorkSectionService();
        $syncCommand->wsService = $this->wsService;
        $syncCommand->output = $this->output;

        /** @var User $user */
        $user = User::first();  //TODO: get user from "last updated" filter

        /** @var Worklog[] $worklogs */
        $worklogs = Worklog::with('issue', 'issue.project')->get();
        foreach ($worklogs as $worklog) {
            $this->output->writeln('Handle Worklog ' . $worklog->jira_id);
            /** @var Issue $issue */
            $issue = $worklog->issue;
            if (!$issue) continue;

            /** @var Project $project */
            $project = $issue->project;
            if (!$project) continue;

            //project
            $projectName = "jira-[" . $project->name . "]-" . $project->key;
            $wsProject = \App\Models\Ws\Project::where('name', $projectName)->first();
            if (!$wsProject) {
                $this->output->writeln('Project [' . $projectName . '] is not Found, try to create them.');
                $wsService->createProject(
                    $user->email, "", "ANY", [$user->email], $projectName,
                    "NOT change the name of the project", $user->company
                );
                $syncCommand->loadProjects($user);
            }
            $wsProject = \App\Models\Ws\Project::where('name', $projectName)->first();
            if (!$wsProject) {
                $this->output->writeln('Project [' . $projectName . '] is not found yet, WTF');
                return;
            }

            //issue
            $this->handleIssue($issue, $user, $wsProject, $worklog);

            // TODO: subtasks
            // foreach ($issue)

        }

    }

    public function handleIssue(Issue $issue, User $user, \App\Models\Ws\Project $wsProject, Worklog $worklog)
    {
        $wsService = $this->wsService;
        $syncCommand = $this->syncCommand;

        $taskName = $issue->summary;
        $this->output->writeln('-- issue ' . $taskName);
        $wsTask = Task::where('name', $taskName)->first();
        if (!$wsTask) {
            $this->output->writeln('Task [' . $taskName . '] is not found, try to create them.');
            $wsService->createTask([
              'page'              => $wsProject->page,
              'email_user_from'   => $user->email,
              'email_user_to'     => $user->email,
              'title'             => $taskName,
              'text'              => $issue->description,
              'datestart'         => $worklog->started->format('d.m.Y')
            ]);
            $syncCommand->loadTasks($user, $wsProject);
        }
        $wsTask = Task::where('name', $taskName)->first();
        if (!$wsTask) {
            $this->output->writeln('Task [' . $taskName . '] is not found yet, WTF');
            return;
        }

        //check task is done
        $resolution = $issue->resolution;
        $status     = $issue->status;
        if (isset($status['name']) && $status['name'] == 'Done' && $wsTask->status != 'done') {
            $updDate = $issue->resolutiondate->format('d.m.Y');
            $wsService->updateTask([
               'page'            => $wsTask->page,
               'dateend'         => $updDate,
            ]);
            $wsService->completeTask(['page' => $wsTask->page]);
            $syncCommand->loadTasks($user, $wsProject);
        }

        $this->handleWorkLog($worklog, $wsTask, $wsProject);
    }

    public function handleWorkLog(Worklog $worklog, Task $wsTask, \App\Models\Ws\Project $wsProject)
    {
        $wsService = $this->wsService;
        $syncCommand = $this->syncCommand;
        //worklog
        $logDate = $worklog->started->format('Y-m-d');

        if (!isset($wsTask->user_from['email'])) return;
        $user = User::where('email', $wsTask->user_from['email'])->orWhere('second_email', $wsTask->user_from['email'])->first();
        if (!$user) return;

        $wsTimeMoney = TimeMoney::where([
          ['date', '=', $logDate],
          ['ws_task_id', '=', $wsTask->ws_id],
          ['user_id', '=', $worklog->user->id]
        ])->first();

        if (!$wsTimeMoney) {
            $this->output->writeln('TimeMoney [' . $logDate . ', ' . $wsTask->ws_id . '] is not found, try to create them.');
            $wsService->createTimeMoney([
              'page'              => $wsTask->page,
              'email_user_from'   => $worklog->user->email,
              'time'              => $worklog->gen_time,
              'date'              => $worklog->started->format('d.m.Y'),
              'comment'           => $worklog->comment,
            ]);
            $syncCommand->loadTimeMoney($wsProject);
        }

        $wsTimeMoney = TimeMoney::where([
          ['date', '=', $logDate],
          ['ws_task_id', '=', $wsTask->ws_id]
        ])->first();
        if (!$wsTimeMoney) {
            $this->output->writeln('TimeMoney [' . $logDate . ', ' . $wsTask->ws_id . '] is not found yet, WTF');
            return;
        }
    }

}
