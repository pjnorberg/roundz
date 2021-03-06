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
            if ($match->finished && $match->playoff) {
                // Feed players to the next round:
                $match->updateNextRound();
            }
            else if ($match->finished && ! $match->playoff) {
                // Set some data for this participant:
                $match->setPoints();
                $match->addGame();
                $match->calculateDiff();

                if ($match->tournament->finishedQualifyingMatches()) {
                    // Populate the playoff now as the qualifying round has been finished:
                    $match->tournament->setupPlayoff();
                }
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

    /**
     * Add points to winners / tie game.
     * @return bool
     */
    public function setPoints()
    {
        // The game is tied, give 1 point each:
        if ($this->home_score == $this->away_score) {
            $this->homeParticipant->points += 1;
            $this->homeParticipant->save();

            $this->awayParticipant->points += 1;
            $this->awayParticipant->save();
            return true;
        }

        // Otherwise give 3 points to winner:
        if ($this->home_score > $this->away_score) {
            $this->homeParticipant->points += 3;
            $this->homeParticipant->save();
            return true;
        }

        if ($this->home_score < $this->away_score) {
            $this->awayParticipant->points += 3;
            $this->awayParticipant->save();
            return true;
        }

        return false;
    }

    /**
     * Add total of games played.
     * @return bool
     */
    public function addGame()
    {
        $this->homeParticipant->games_played += 1;
        $this->homeParticipant->save();
        $this->awayParticipant->games_played += 1;
        $this->awayParticipant->save();
        return true;
    }

    /**
     * Update game difference in goals.
     */
    public function calculateDiff()
    {
        // For home participant:
        $sum = ($this->home_score) - ($this->away_score);
        $this->homeParticipant->diff += $sum;
        $this->homeParticipant->save();

        // For away participant:
        $sum = ($this->away_score) - ($this->home_score);
        $this->awayParticipant->diff += $sum;
        $this->awayParticipant->save();
    }
}
