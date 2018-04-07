<?php

namespace App\Models\Jira;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'jira_projects';

    protected $fillable = [
        'expand',
        'self',
        'jira_id',
        'key',
        'name',
        'avatarUrls',
        'projectTypeKey',
        'user_id',
    ];

    protected $casts = [
        'avatarUrls' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
