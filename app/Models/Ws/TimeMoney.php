<?php

namespace App\Models\Ws;

use Illuminate\Database\Eloquent\Model;

class TimeMoney extends Model
{
    protected $table = 'ws_time_moneys';

    protected $fillable = [
        'ws_id',
        'ws_task_id',
        'jsonTask', 'comment',
        'time', 'money', 'date', 'is_timer',
        'user_from',
    ];

    protected $casts = [
        'jsonTask'  => 'array',
        'user_from' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'ws_task_id', 'ws_id');
    }
}
