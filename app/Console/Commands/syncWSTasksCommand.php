<?php

namespace App\Console\Commands;

use App\Models\Jira\Issue;
use App\Models\Jira\Project;
use App\Models\Jira\Session;
use App\Models\Jira\Worklog;
use App\Models\Ws\Tag;
use App\Models\Ws\Task;
use App\Models\Ws\TimeMoney;
use App\Services\Api\WorkSectionService;
use App\User;
use Atlassian\JiraRest\Facades\Jira;
use Atlassian\JiraRest\Helpers\Projects;
use Carbon\Carbon;
use Illuminate\Console\Command;

class syncWSTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ws:syncTasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync WS tasks.';

    /** @var WorkSectionService $wsService */
    protected $wsService;

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
        $this->wsService = $wsService = new WorkSectionService();
        /** @var User $user */
        $user = User::first();
        // $this->loadSession($user);
        $projects = $this->loadProjects($user);
        /** @var \App\Models\Ws\Project $project */
        foreach ($projects as $project) {
            $tasks = $this->loadTasks($user, $project);
            $this->loadTimeMoney($project);
        }
        $this->output->writeln('All Done!! mazafaka');

    }

    public function loadSession(User $user)
    {
        /** @var Session $session */
        $session = $user->jiraSessions()->latest()->first();

        if (!$session) {
            $session = new Session(['user_id' => $user->id]);
            $this->output->writeln('Create jira session entity.');
            //throw new \Exception('The JIRA session is not present for this user ['.$user->id.']');
        }

        cache()->put('jira_cookie', json_encode($session->cookie), 60*60*24*365);

        try {
            $session_json = \jira()->session()->get();
        } catch (\Exception $exception) {
            $cookie = \jira()->session()->login(config('atlassian.jira.auth.basic.username'), config('atlassian.jira.auth.basic.password'));    //TODO: reauth usage
            $session->cookie = json_decode($cookie);
            $session->save();
            cache()->put('jira_cookie', json_encode($session->cookie), 60*60*24*365);
        }
    }

    /**
     * @param User $user
     *
     * @return array|\App\Models\Ws\Project[]
     */
    public function loadProjects(User $user)
    {
        $projects  = $this->wsService->getProjects();
        $return = [];

        foreach ($projects as $project) {
            /**
             * array:6 [
            "name" => "Blah Blah Blah"
            "page" => "/project/123456/"
            "status" => "active"
            "company" => "Requestum"
            "user_from" => array:2 [
            "email" => "some_email@requestum.com"
            "name" => "Some Name"
            ]
            "user_to" => array:2 [
            "email" => "some_email2@requestum.com"
            "name" => "Some Name2"
            ]
            ]

             */
            $ids = explode("/", $project['page']);
            $id = $ids[2];
            $this->output->writeln('Check project page['.$project['page'].'].');
            if (!is_numeric($id)) {
                $this->output->writeln('Project with page['.$project['page'].'] does not have a ID.');
                continue;
            }
            /** @var \App\Models\Ws\Project $project */
            $prjObj = \App\Models\Ws\Project::firstOrNew(['ws_id' => $id]);
            $prjObj->__construct(array_merge($project, ['ws_id' => $id]));
            $prjObj->save();
            $return[] = $prjObj;
        }
        return $return;
    }

    /**
     * Load tasks.
     *
     * @param User                   $user
     * @param \App\Models\Ws\Project $project
     *
     * @return array
     * @throws \App\Exceptions\ApiException
     */
    public function loadTasks(User $user, \App\Models\Ws\Project $project)
    {
        $tasks = $this->wsService->getTasks($project->page);
        /*
         array:2 [
  "status" => "ok"
  "data" => array:5 [
    0 => array:8 [
      "name" => "WIKI"
      "page" => "/project/12345/6789/"
      "status" => "active"
      "priority" => "10"
      "user_from" => array:2 [
        "email" => "some_email@requestum.com"
        "name" => "Some Email"
      ]
      "user_to" => array:2 [
        "email" => "Любой сотрудник"
        "name" => "Любой сотрудник"
      ]
      "date_added" => "2018-03-15 18:32"
      // "date_end" => "2018-03-23"
      // "date_start" => "2018-03-23"
      // "date_closed" => "2018-03-28 22:16"
      // "max_time" => 6
      "tags" => array:1 [
        0 => "Docs"
      ]
      "child" => array:6 [
        0 => array:9 [
    ]
    1 =>
         */

        $result = [];

        foreach ($tasks as $task) {
            $this->output->writeln('Parse Task page ' . $task['page']);
            $result[] = $this->parseTask($project, $task);
        }

        return $result;
    }

    protected function parseTask(\App\Models\Ws\Project $project, $data, Task $parentTask = null)
    {
        $ids = explode("/", $data['page']);

        while (sizeof($ids) > 0 && $ids[sizeof($ids)-1] == "")
            array_pop($ids);

        $id = $ids[sizeof($ids)-1];

        if (!is_numeric($id)) {
            $this->output->writeln("Task with page ".$data['page'].' is not found.');
            return null;
        }

        /** @var Task $task */
        $task = Task::firstOrNew(['ws_id' => $id]);
        $child = isset($data['child']) ? $data['child'] : null;
        $task->__construct(array_merge($data, ['ws_project_id' => $project->ws_id]));
        if ($parentTask) $task->parent()->associate($parentTask);
        $task->save();

        //tags
        if (isset($data['tags'])) {
            foreach ($data['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $task->tags()->attach($tag->id);
            }
        }

        if ($child) {
            $this->output->writeln('Found child tasks ');
            foreach ($child as $item) {
                $this->parseTask($project, $item, $task);
            }
            $task->refresh();
        }

        return $task;
    }

    public function loadTimeMoney(\App\Models\Ws\Project $project)
    {
        $this->output->writeln('Let\'s find timeMoney for project ' . $project->page);
        $data = $this->wsService->getTimeMoney($project->page);
        /**
        {
            "task": {
                "page": "/project/PROJECT_ID/TASK_ID/",
                "name": "TASK TITLE"
            },
            "comment": "User comment",
            "time": "1:30",
            "money": "20.50",
            "date": " YYYY-MM-DD",
            "is_timer": false,
            "user_from": {
                "email": "USER_EMAIL",
                "name": "USER_NAME"
            }
        }
         */
        /*
            array:9 [
              "id" => "12345"
              "task" => array:4 [
                "page" => "/project/12345/12345/12345/"
                "name" => "Dynamic Finish screen"
                "status" => "active"
                "parent" => "Transition to External Pump"
              ]
              "project" => array:2 [
                "page" => "/project/12345/"
                "name" => "Name of the Project"
              ]
              "comment" => "1st Workflow Logic"
              "time" => "1:00"
              "money" => "0.00"
              "date" => "2018-04-08"
              "is_timer" => false
              "user_from" => array:2 [
                "email" => "no_email@requestum.com"
                "name" => "NoSome Email"
              ]
            ]

         */
        foreach ($data as $timeMoneyArr) {
            $timeMoneyArr['ws_id'] = $timeMoneyArr['id'];
            $timeMoneyArr['jsonTask']   = $timeMoneyArr['task'];
            $this->output->writeln('ON ' . $project->page . ' IS found timeMoney ' . $timeMoneyArr['ws_id']);
            $ids = isset($timeMoneyArr['task']) && isset($timeMoneyArr['task']['page']) ? explode("/", $timeMoneyArr['task']['page']) : null;
            if (!$ids)
                continue;

            /** @var TimeMoney $timeMoney */
            $timeMoney = TimeMoney::firstOrNew(['id' => $timeMoneyArr['id']]);
            $timeMoney->__construct($timeMoneyArr);

            while (sizeof($ids) > 0 && $ids[sizeof($ids)-1] == "")
                array_pop($ids);

            $task_id = $ids[sizeof($ids)-1];
            $task = Task::where('ws_id', $task_id)->first();

            if (!$task) {
                $this->output->writeln('Task ' . $task_id . ' is not Found. WTF');
                continue;
            }

            $timeMoney->task()->associate($task);
            $timeMoney->save();
        }
    }


}
