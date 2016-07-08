<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $table = 'tournaments';
    protected $fillable = ['user_id'];

    /**
     * Validation:
     */

    public function rules()
    {
        return [
            'name' => 'required',
            'slug' => 'required|unique:tournaments,slug,'.$this->id,
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Your tournament needs a valid name, because you know, it would not work otherwise.',
            'slug.required' => 'Your tournament needs a slug to be accessible.',
            'slug.unique' => 'Your tournament slug has already been taken.',
        ];
    }

    /**
     * Relationships:
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function matches()
    {
        return $this->hasMany(Match::class)->orderBy('round', 'ASC');
    }

    public function finishedQualifyingMatches()
    {
        return ! $this->qualifyingMatches()->where('finished', 0)->count();
    }

    public function playoffMatches()
    {
        return $this->hasMany(Match::class)->where('playoff', 1)->orderBy('round', 'ASC');
    }

    public function qualifyingMatches()
    {
        return $this->hasMany(Match::class)->where('playoff', 0)->orderBy('round', 'ASC');
    }

    /**
     * Helpers
     */

    public function getRounds()
    {
        return $this->playoffMatches()->select('round')->groupBy('round')->get()->pluck('round');
    }

    public function getQualifyingTable($qualified = false)
    {
        $table = $this->participants()
            ->orderBy('points', 'DESC')
            ->orderBy('diff', 'DESC')
            ->orderBy('games_played', 'DESC')
        ;

        if ($qualified && $this->playoff_size) {
            $table->limit($qualified);
        }

        return $table->get();
    }

    /**
     * Populate the first round of playoff games with the top participants.
     */
    public function setupPlayoff()
    {
        $playoffSize = $this->playoff_size;
        $matches = $this->playoffMatches()->where('round', 1)->get();
        $participants = $this->getQualifyingTable($playoffSize);
        $half = $participants->count() / 2;
        $participantArray = array_chunk($participants->toArray(), $half);

        // First half should make up home teams:
        foreach ($matches as $index => $match) {
            $match->home_participant_id = $participantArray[0][$index]['id'];
            $match->save();
        }

        // Let second half make up away teams:
        foreach ($matches as $index => $match) {
            $match->away_participant_id = $participantArray[1][$index]['id'];
            $match->save();
        }
    }
}
