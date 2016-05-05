<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participants';

    /**
     * Validation:
     */

    public function rules()
    {
        return [
            'name' => 'required',
            'tournament_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Participants must have a name.',
            'tournament_id.required' => 'Participants must belong to a tournament.',
        ];
    }

    /**
     * Relationships:
     */

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
