<?php

namespace App\Models\Jira;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'jira_sessions';

    protected $fillable = [
        'user_id',
        'cookie'
    ];

    protected $casts = [
        'cookie'    => 'array'
    ];
}
