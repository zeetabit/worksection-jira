<?php

namespace App\Console\Commands;

use App\Models\Jira\Issue;
use App\Models\Jira\Project;
use App\Models\Jira\Session;
use App\Models\Jira\Worklog;
use App\User;
use Atlassian\JiraRest\Facades\Jira;
use Atlassian\JiraRest\Helpers\Projects;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class syncJiraTasksCommand extends Command
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
    protected $description = 'Sync JIRA tasks.';

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
        foreach (User::all() as $user) {
            if (strlen($user->jira_password) == 0) continue;

            /** @var User $user */
            //$user = User::first();
            $this->loadSession($user);
            $this->loadProjects($user);
            $issues = $this->loadIssues($user);
            $this->loadWorkLog($user, $issues);
        }
    }

    public function loadSession(User $user)
    {
        /** @var Session $session */
        $session = $user->jiraSessions()->latest()->first();

        if (!$session) {
            $session = new Session(['user_id' => $user->id]);
            $this->output->writeln('Create jira session entity.');
            $pass = Crypt::decryptString($user->jira_password);
            $cookie = \jira()->session()->login($user->jira_user, $pass);
            $session->cookie = json_decode($cookie);
            $session->save();
            //throw new \Exception('The JIRA session is not present for this user ['.$user->id.']');
        }

        cache()->put('jira_cookie', json_encode($session->cookie), 60*60*24*365);

        try {
            $session_json = \jira()->session()->get();
        } catch (\Exception $exception) {
            $pass = Crypt::decryptString($user->jira_password);
            $cookie = \jira()->session()->login($user->jira_user, $pass);
            $session->cookie = json_decode($cookie);
            $session->save();
            cache()->put('jira_cookie', json_encode($session->cookie), 60*60*24*365);
        }
    }

    /**
     * @param User $user
     *
     * @throws \Atlassian\JiraRest\Exceptions\JiraClientException
     * @throws \Atlassian\JiraRest\Exceptions\JiraNotFoundException
     * @throws \Atlassian\JiraRest\Exceptions\JiraUnauthorizedException
     * @throws \TypeError
     */
    public function loadProjects(User $user)
    {
        $projects = \jira()->projects()->all();
        foreach ($projects as $project) {
            $project['jira_id'] = $project['id'];
            $project = array_merge($project, ['user_id' => $user->id]);
            unset($project['id']);
            /** @var Project $prjObj */
            $prjObj = Project::firstOrNew(['user_id' => $user->id, 'jira_id' => $project['jira_id']]);
            $prjObj->__construct($project);
            $prjObj->save();
        }
    }

    /**
     * Load last updated issues.
     *
     * @param User $user
     *
     * @return array
     * @throws \Atlassian\JiraRest\Exceptions\JiraClientException
     * @throws \Atlassian\JiraRest\Exceptions\JiraNotFoundException
     * @throws \Atlassian\JiraRest\Exceptions\JiraUnauthorizedException
     * @throws \TypeError
     */
    public function loadIssues(User $user)
    {
        $issues = \jira()->issues()->search(['jql' => "worklogDate >= startOfMonth() AND worklogAuthor = currentUser()"]);
        // TODO: pagination
        //       array:5 [
        //     "expand" => "names,schema"
        // "startAt" => 0
        // "maxResults" => 50
        // "total" => 1
        // "issues" => array:1 [

        $result = [];

        foreach ($issues['issues'] as $issue) {
            $fromFields = ['issuetype', 'components', 'timespent', 'timeoriginalestimate', 'description', 'project',
              'fixVersions', 'aggregatetimespent', 'resolution', 'aggregatetimeestimate', 'resolutiondate', 'workratio',
              'summary', 'lastViewed', 'watches', 'creator', 'subtasks', 'created', 'reporter', 'aggregateprogress',
              'priority', 'labels', 'environment', 'timeestimate', 'aggregatetimeoriginalestimate', 'versions', 'duedate',
              'progress', 'issuelinks', 'votes', 'assignee', 'updated', 'status'];

            foreach ($fromFields as $field)
                $issue[$field] = $issue['fields'][$field];

            if (strlen($issue['description']) == 0)
                $issue['description'] = "";

            $obj = Issue::where([
              ['jira_id', '=', $issue['id']],
              ['jira_project_id', '=', $issue['project']['id']]
            ])->first();

            if (!$obj) $obj = new Issue(['jira_id' => $issue['id'], 'jira_project_id' => $issue['project']['id']]);

            $obj->__construct($issue);
            $obj->save();
            $result[] = $obj;
        }
        return $result;
    }

    /**
     * @param User $user
     * @param Issue[] $issues
     */
    public function loadWorkLog(User $user, $issues)
    {
        foreach ($issues as $issue)
        {
            //TODO: pagination
            /**
             * array:4 [
            "startAt" => 0
            "maxResults" => 6
            "total" => 6
            "worklogs" => array:6 [
             */
            $worklogs = \jira()->issue($issue->jira_id . '/worklog')->get();
            foreach ($worklogs['worklogs'] as $worklog) {
                $worklog['jira_id'] = $worklog['id'];
                $worklog['jira_issue_id'] = $worklog['issueId'];
                $user = User::where('email', $worklog['author']['emailAddress'])->orWhere('second_email', $worklog['author']['emailAddress'])->first();
                if (!$user) {
                    // because user may be in not our team
                    // $user = User::create([
                    //     'name'  => $worklog['author']['name'],
                    //     'email' => $worklog['author']['emailAddress'],
                    //     'password'  => 'nopassword',
                    // ]);
                    continue;
                }
                $worklog['user_id'] = $user->id;
                /** @var Worklog $workLogObj */
                $workLogObj = Worklog::where([
                    ['jira_id', '=', $worklog['jira_id']],
                    ['jira_issue_id', '=', $worklog['jira_issue_id']]
                ])->first();

                if (!$workLogObj) $workLogObj = new Worklog(['jira_id' => $worklog['jira_id'], 'jira_issue_id' => $worklog['jira_issue_id']]);

                $workLogObj->__construct($worklog);
                $workLogObj->save();
            }
        }
    }


}
