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

    /**
     * Helpers
     */

    public function winner()
    {
        if ( ! $this->finished) {
            return false;
        }

        return $this->home_score > $this->away_score ? $this->homeParticipant : $this->awayParticipant;
    }

    public function createQualifierMatch($match)
    {
        $this->round = null;
        $this->home_participant_id = $match['home_participant_id'];
        $this->away_participant_id = $match['away_participant_id'];
        $this->playoff = 0;
    }

    public function createPlayoffMatch($match)
    {
        $this->round = $match['round'];
        $this->home_team_from = $match['home_team_from'];
        $this->away_team_from = $match['away_team_from'];
        $this->home_participant_id = $match['home_participant_id'];
        $this->away_participant_id = $match['away_participant_id'];
        $this->playoff = 1;
    }
}
