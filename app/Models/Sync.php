<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sync extends Model
{

    public function syncable()
    {
        return $this->morphTo();
    }

}
