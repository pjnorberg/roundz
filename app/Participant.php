<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participants';

    /**
     * Relationships:
     */

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
