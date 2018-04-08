# worksection-jira
Worksection and jira projects, task, worklog synhronization.

# howto install

1) copy .env from .env.example and fill DB, JIRA, WS params
2) composer install
3) php artisan key:generate
4) php artisan migrate

# available commands

1) Load from JIRA

    Command for load all projects, issues and WorkLog for last week:
    
    `command:jira:syncTasks`

2) Sync with WS the loaded from JIRA tasks

    This command will push to WorkSection projects, issues and worklogs.
    
    `command:ws:syncTasks`
