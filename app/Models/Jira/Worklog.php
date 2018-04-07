<?php

namespace App\Models\Jira;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Worklog extends Model
{
    protected $table = 'jira_worklogs';

    protected $fillable = [
        'self',
        'author',
        'updateAuthor',
        'comment',
        'created',
        'updated',
        'started',
        'timeSpent',
        'timeSpentSeconds',
        'jira_id',
        'jira_issue_id',
    ];

    protected $casts = [
        'author'    => 'array',
        'updateAuthor' => 'array',
        'created'   => 'datetime',
        'updated'   => 'datetime',
        'started'   => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        foreach ($this->casts as $attrName => $attrType) {
            if ($attrType == 'datetime' && isset($attributes[$attrName])) {    //for nulled dates
                if ($attributes[$attrName] == null)
                    $attributes[$attrName] = date('Y-m-d H:i:s', 0);
                else    //возможно ISO dateTime прилетело
                    $attributes[$attrName] = Carbon::parse($attributes[$attrName]);
            }
        }
        parent::__construct($attributes);
    }
}
