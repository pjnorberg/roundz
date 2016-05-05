<?php

namespace App\Http\Controllers;

use App\Participant;
use App\Tournament;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;

use App\Http\Requests;

class ParticipantsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $participants = [];

        if ($request->has('tournament_id')) {
            $participants = Participant::where('tournament_id', $request->get('tournament_id'))->get();
        }

        return response()->json($participants);
    }

    public function store(Request $request)
    {
        $json = [];

        try {
            $participant = new Participant();
            $this->validate($request, $participant->rules(), $participant->messages());
            $participant->name = $request->get('name');
            $participant->tournament_id = $request->get('tournament_id');
            $participant->save();

            $json = [
                'response' => 'success',
                'id' => $participant->id
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
}