<?php

namespace App\Models\Jira;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $table = 'jira_issues';

    protected $fillable = [
        'expand',
        'jira_id',
        'key',
        'issuetype',
        'components',
        'timespent',
        'timeoriginalestimate',
        'description',
        'jira_project_id',
        'fixVersions',
        'aggregatetimespent',
        'resolution',
        'aggregatetimeestimate',
        'resolutiondate',
        'workratio',
        'summary',
        'lastViewed',
        'watches',
        'creator',
        'subtasks',
        'created',
        'reporter',
        'aggregateprogress',
        'priority',
        'labels',
        'timeestimate',
        'aggregatetimeoriginalestimate',
        'versions',
        'duedate',
        'progress',
        'issuelinks',
        'votes',
        'assignee',
        'updated',
        'status',

    ];

    protected $casts = [
        'issuetype' => 'array',
        'components'=> 'array',
        'fixVersions'=>'array',
        'resolution'=> 'array',
        'resolutiondate' => 'datetime',
        'lastViewed'     => 'datetime',
        'watches'   => 'array',
        'creator'   => 'array',
        'subtasks'  => 'array',
        'created'   => 'datetime',
        'reporter'  => 'array',
        'aggregateprogress' => 'array',
        'priority'  => 'array',
        'labels'    => 'array',
        'versions'  => 'array',
        'duedate'   => 'datetime',
        'progress'  => 'array',
        'issuelinks'=> 'array',
        'votes'     => 'array',
        'assignee'  => 'array',
        'updated'   => 'datetime',
        'status'    => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        foreach ($this->casts as $attrName => $attrType) {
            if ($attrType == 'datetime' && isset($attributes[$attrName])) {    //for nulled dates
                if ($attributes[$attrName] == null)
                    $attributes[$attrName] = date('Y-m-d H:i:s', 0);
                else    //возможно ISO dateTime прилетело
                    $attributes[$attrName] = Carbon::parse($attributes[$attrName]);
            } elseif ($attrType == 'datetime' && !isset($attributes[$attrName])) {
                $attributes[$attrName] = date('Y-m-d H:i:s', 0);
            } elseif ($attrType == 'array' && !isset($attributes[$attrName])) {
                $attributes[$attrName] = "";
            }
        }

        if (isset($attributes['description']) && $attributes['description'] == null)
            $attributes['description'] = "nodescr";

        parent::__construct($attributes);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'jira_project_id', 'jira_id');
    }
}
