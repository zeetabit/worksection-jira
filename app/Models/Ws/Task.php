<?php

namespace App\Models\Ws;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'ws_tasks';

    protected $fillable = [
        'ws_id', 'ws_parent_task_id',
        'name', 'page', 'status', 'priority',
        'user_from', 'user_to',
        'date_added', 'date_closed',
        'date_start', 'date_end',
        'ws_project_id'
    ];

    protected $casts = [
        'user_from' => 'array',
        'user_to'   => 'array',
        'date_added'=> 'DateTime',
        'date_closed'=>'DateTime',
        'date_start'=> 'DateTime',
        'date_end'  => 'DateTime',
    ];

    public function __construct(array $attributes = [])
    {
        foreach ($this->casts as $attrName => $attrType) {
            if ($attrType == 'DateTime' && isset($attributes[$attrName])) {    //for nulled dates
                if ($attributes[$attrName] == null)
                    $attributes[$attrName] = new Carbon(0);
                elseif ($attrName == "date_added" || $attrName == "date_closed")    //возможно ISO dateTime прилетело
                    $attributes[$attrName] = Carbon::createFromFormat("Y-m-d H:i", $attributes[$attrName]);
                else
                    $attributes[$attrName] = Carbon::parse($attributes[$attrName]);
            } else {
                $attributes[$attrName] = new Carbon(0);
            }
        }
        parent::__construct($attributes);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'ws_project_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'ws_parent_task_id', 'ws_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'ws_parent_task_id', 'ws_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'ws_tag_tasks', 'ws_task_id', 'ws_tag_id', 'id', 'id');
    }

    public function timeMoneys()
    {
        return $this->hasMany(TimeMoney::class, 'ws_task_id', 'ws_id');
    }
}
