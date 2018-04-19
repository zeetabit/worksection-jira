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
use Illuminate\Support\Facades\Crypt;

class addUserJiraReqCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:user:setJiraReq';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set jira Password and login';

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
        $email = $this->ask('email');
        $user_name = $this->ask('user');
        $password = $this->ask('password');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'email'   => $email,
                'name'    => $user_name,
                'second_email'  => '',
                'company' => ''
            ]);
        }

        $user->jira_user = $user_name;
        $user->jira_password = Crypt::encryptString($password);
        $user->save();

        $this->output->writeln('All Done!! mazafaka');

    }
}
