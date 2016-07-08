<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Match;
use App\Participant;
use App\Tournament;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    public function index()
    {
        if (Auth::user()) {
            return redirect()->route('tournaments.index');
        }

        return view('index');
    }

    public function show($slug)
    {
        $tournament = Tournament::where('slug', $slug)->first();
        $rounds = $tournament->getRounds();
        $participants = $tournament->getQualifyingTable();

        return view('show', compact('tournament', 'rounds', 'participants'));
    }
}
