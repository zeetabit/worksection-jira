<?php

namespace App\Models\Ws;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'ws_projects';

    protected $fillable = ['ws_id', 'name', 'page', 'status', 'company', 'user_from', 'user_to'];

    protected $casts = [
        'user_from' => 'array',
        'user_to'   => 'array'
    ];
}
