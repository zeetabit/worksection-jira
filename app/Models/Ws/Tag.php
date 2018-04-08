<?php

namespace App\Models\Ws;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'ws_tags';

    protected $fillable = ['name'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'ws_tag_tasks');
    }
}
