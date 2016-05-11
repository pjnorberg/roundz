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
        return $this->hasMany(Participant::class)->orderBy('created_at', 'ASC');
    }

    public function matches()
    {
        return $this->hasMany(Match::class)->orderBy('round', 'ASC');
    }

    public function playoffMatches()
    {
        return $this->hasMany(Match::class)->where('playoff', 1)->orderBy('round', 'ASC');
    }

    public function qualifyingMatches()
    {
        return $this->hasMany(Match::class)->where('playoff', 0)->orderBy('round', 'ASC');
    }
}
