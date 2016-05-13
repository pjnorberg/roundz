<?php

namespace App\Http\Controllers;

use App\Match;
use App\Participant;
use App\Tournament;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;

use App\Http\Requests;

class MatchesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Validation:
     */

    public function rules()
    {
        return [
            'tournament_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'tournament_id.required' => 'Participants must belong to a tournament.',
        ];
    }

    public function store(Request $request)
    {
        $json = [];

        // We need to map IDs between the JS application and our DBMS:
        $map = [];

        try {
            if ($request->has('playoffMatches') && $request->get('playoffMatches')) {
                foreach ($request->get('playoffMatches') as $matchData) {
                    $relativeId = $matchData['id'];
                    $match = new Match();

                    // Set real ID if we find relative ID already in array:
                    if (array_key_exists($matchData['home_team_from'], $map)) {
                        $matchData['home_team_from'] = $map[$matchData['home_team_from']];
                    }
                    if (array_key_exists($matchData['away_team_from'], $map)) {
                        $matchData['away_team_from'] = $map[$matchData['away_team_from']];
                    }

                    $match->tournament_id = $request->get('tournamentId');
                    $match->createPlayoffMatch($matchData);
                    $match->save();

                    // We map this by simply saving the created ID:
                    $map[$relativeId] = $match->id;
                }
            }

            if ($request->has('qualifyingMatches') && $request->get('qualifyingMatches')) {
                foreach ($request->get('qualifyingMatches') as $matchData) {
                    $match = new Match();
                    $match->tournament_id = $request->get('tournamentId');
                    $match->createQualifierMatch($matchData);
                    $match->save();
                }
            }

            $json = [
                'response' => 'success'
            ];
        }
        catch (ValidationException $e) {
            $json['response'] = $e->getMessage();
        }
        catch (\Exception $e) {
            $json['response'] = $e->getMessage();
        }

        return response()->json($json);
    }

    public function destroy($id)
    {
        $json = [];

        try {
            $tournament = Participant::findOrFail($id);
            $tournament->delete();
            $json['response'] = 'success';
        }
        catch (\Exception $e) {
            $json['response'] = $e->getMessage();
        }

        return response()->json($json);
    }

    public function edit($id)
    {
        $match = Match::find($id);

        return view('matches.edit', compact('match'));
    }

    public function update(Request $request, $id)
    {
        try {
            $match = Match::findOrFail($id);
            $match->home_score = $request->get('home_score');
            $match->away_score = $request->get('away_score');
            $match->save();

            if ($request->has('finished')) {
                $match->finished = $request->get('finished');
                $match->save();
                return redirect()->route('app.show', [$match->tournament->slug]);
            }

            return back()->with('success_message', 'Your match has been updated.');
        }
        catch (ValidationException $e) {
            return back()->withErrors()->withInput();
        }
    }
}