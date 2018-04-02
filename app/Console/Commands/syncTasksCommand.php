<?php

namespace App\Console\Commands;

use App\Models\Jira\Session;
use App\User;
use Atlassian\JiraRest\Facades\Jira;
use Atlassian\JiraRest\Helpers\Projects;
use Illuminate\Console\Command;

class syncTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:jira:syncTasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync tasks';

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
     */
    public function handle()
    {
        /** @var User $user */
        $user = User::first();
        $this->loadSession($user);
        $projects = \jira()->projects()->all();
        $project = $projects[0];
        $issues = \jira()->issues()->search();
        dd($issues);
    }

    public function loadSession(User $user)
    {
        /** @var Session $session */
        $session = $user->jiraSessions()->latest()->first();

        if (!$session) {
            throw new \Exception('The JIRA session is not present for this user ['.$user->id.']');
        }

        cache()->put('jira_cookie', json_encode($session->cookie), 60*60*24*365);
        $session_json = \jira()->session()->get();
    }
}
