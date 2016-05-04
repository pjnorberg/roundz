<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $table = 'matches';

    /**
     * Relationships:
     */

    public function homeParticipant()
    {
        return $this->belongsTo(Participant::class, 'home_participant_id');
    }

    public function awayParticipant()
    {
        return $this->belongsTo(Participant::class, 'away_participant_id');
    }
}
