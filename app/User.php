<?php

namespace App;

use App\Models\Jira\Project;
use App\Models\Jira\Session;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function jiraSessions()
    {
        return $this->hasMany(Session::class, 'user_id', 'id');
    }

    public function jiraProjects()
    {
        return $this->hasMany(Project::class, 'user_id', 'id');
    }
}
