<?php

namespace App\Http\Controllers;

use App\Participant;
use App\Tournament;
use Illuminate\Http\Request;

use App\Http\Requests;

class ApiController extends Controller
{
    public function index(Request $request)
    {
        $participants = [];

        if ($request->has('tournament_id')) {
            $participants = Participant::where('tournament_id', $request->get('tournament_id'))->get();
        }

        return response()->json($participants);
    }
}