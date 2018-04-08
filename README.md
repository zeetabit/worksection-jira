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

2) Load from WS

    This command for load add proj, tasks, timemoney's.
    
    `command:ws:syncTasks`

3) Sync

    This command will push the WS entities to WorkSection projects, issues and worklogs.
    
    For sync we have a `syncable` entity for each other JIRA projects to customized selected WS projects.
    
    PS: project could not be create in WS, only associating.
    
    `TODO`