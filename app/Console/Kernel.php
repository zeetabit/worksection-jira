<?php

namespace App\Console;

use App\Console\Commands\addUserJiraReqCommand;
use App\Console\Commands\jira2WsTasksCommand;
use App\Console\Commands\syncJiraTasksCommand;
use App\Console\Commands\syncWSTasksCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // public static $cache_sync_jira = 'sync_jira_working', $cache_sync_ws = 'sync_ws_wonrking', $cache_jira_2_ws = 'jira_2_ws_working';

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        syncJiraTasksCommand::class,
        syncWSTasksCommand::class,
        addUserJiraReqCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // $syncJiraLog = storage_path('logs/sync_jira.log');
        // if (!file_exists($syncJiraLog)) {
        //     file_put_contents($syncJiraLog, '');
        // }
        // $syncWsLog = storage_path('logs/sync_ws.log');
        // if (!file_exists($syncWsLog)) {
        //     file_put_contents($syncWsLog, '');
        // }
        // $syncJira2wsLog = storage_path('logs/jira2ws.log');
        // if (!file_exists($syncJira2wsLog)) {
        //     file_put_contents($syncJira2wsLog, '');
        // }
        //
        // $schedule->command(syncJiraTasksCommand::class)
        //   ->cron("* */6 * * *")
        //   ->when(function() {
        //       return !cache()->has(self::$cache_jira_2_ws) && !cache()->has(self::$cache_sync_jira) && !cache()->has(self::$cache_sync_ws);
        //   })
        //   ->before(function() {
        //       cache()->set(self::, true);
        //   })
        //   ->then(function() {
        //       cache()->set('sync_jira_working', true);
        //   })
        //   ->withoutOverlapping()
        //   ->appendOutputTo($syncJiraLog);
        //
        // $schedule->command(syncWSTasksCommand::class)
        //   ->cron("* */6 * * *")
        //   ->withoutOverlapping()
        //   ->appendOutputTo($syncWsLog);
        //
        // $schedule->command(jira2WsTasksCommand::class)
        //   ->cron("* */6 * * *")
        //   ->withoutOverlapping()
        //   ->appendOutputTo($syncJira2wsLog);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
