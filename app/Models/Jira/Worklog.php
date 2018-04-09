<?php

namespace App\Models\Jira;

use App\User;
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
        'user_id',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Issue|null
     */
    public function issue()
    {
        return $this->belongsTo(Issue::class, 'jira_issue_id', 'jira_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getGenTimeAttribute()
    {
        $minutes = $this->timeSpentSeconds / 60;    //minutes
        $hours  = floor($minutes / 60);
        $minutes = $minutes % 60;
        return ($hours < 10 ? '0' . $hours : $hours) . ":" . ($minutes < 10 ? '0' . $minutes : $minutes);
    }
}
