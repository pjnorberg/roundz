<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $table = 'matches';

    /**
     * Relationships:
     */

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function homeParticipant()
    {
        return $this->belongsTo(Participant::class, 'home_participant_id');
    }

    public function awayParticipant()
    {
        return $this->belongsTo(Participant::class, 'away_participant_id');
    }

    public function homeTeamFrom()
    {
        return $this->belongsTo(Match::class, 'home_team_from');
    }

    public function awayTeamFrom()
    {
        return $this->belongsTo(Match::class, 'away_team_from');
    }

    /**
     * Model events
     */

    public static function boot()
    {
        parent::boot();
        static::saved(function($match)
        {
            if ($match->finished) {
                $match->updateNextRound();
            }
        });
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

    public function hasTeams()
    {
        return $this->homeParticipant && $this->awayParticipant;
    }

    public function getStatus()
    {
        if ($this->finished) {
            return 'Finished';
        }

        if ( ! $this->hasTeams()) {
            return 'Undecided';
        }

        return 'Active';
    }

    public function potentialParticipants($team)
    {
        $teams = null;

        if ( ! $this->homeTeamFrom || ! $this->awayTeamFrom) {
            return false;
        }

        switch ($team) {
            case 'home' :
                $teams = [
                    $this->homeTeamFrom->homeParticipant ? $this->homeTeamFrom->homeParticipant->name : null,
                    $this->homeTeamFrom->awayParticipant ? $this->homeTeamFrom->awayParticipant->name : null,
                ];
                break;
            case 'away' :
                $teams = [
                    $this->awayTeamFrom->homeParticipant ? $this->awayTeamFrom->homeParticipant->name : null,
                    $this->awayTeamFrom->awayParticipant ? $this->awayTeamFrom->awayParticipant->name : null,
                ];
                break;
        }

        return $teams && isset($teams[0]) && isset($teams[1]) ? implode(' / ', $teams) : false;
    }

    public function updateNextRound()
    {
        $rounds = $this->tournament->getRounds();
        $nextRound = $this->round + 1;

        if ($nextRound <= count($rounds)) {
            $nextMatch = Match::where('round', $nextRound)->where(function($q) {
                $q->where('home_team_from', $this->id)->orWhere('away_team_from', $this->id);
            })->first();

            if ($nextMatch) {
                if ($nextMatch->home_team_from == $this->id) {
                    $nextMatch->home_participant_id = $this->winner()->id;
                }
                else {
                    $nextMatch->away_participant_id = $this->winner()->id;
                }
                $nextMatch->save();
            }
        }

        return true;
    }

    public function type()
    {
        return $this->playoff ? 'Playoff' : 'Qualifier';
    }

    public function createQualifierMatch($match)
    {
        $this->round = $match['round'];
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
