<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Tournament;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tournaments = Auth::user()->tournaments;

        return view('index', compact('tournaments'));
    }

    public function edit($id)
    {
        try {
            $tournament = Tournament::findOrFail($id);
            return view('edit', compact('tournament'));
        }
        catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tournament = Tournament::findOrFail($id);
            $this->validate($request, $tournament->rules(), $tournament->messages());

            $tournament->name = $request->get('name');
            $tournament->slug = $request->get('slug');
            $tournament->instagram_tag = $request->get('instagram_tag');

            $tournament->save();

            return back()->with('success_message', 'Your tournament has been updated.');
        }
        catch (ValidationException $e) {
            return back()->withErrors()->withInput();
        }
    }

    public function show($slug)
    {
        $tournament = Tournament::where('slug', $slug)->where('user_id', Auth::user()->id)->first();
        $initialRound = 1;
        $initialGame = 1;

        return view('show', compact('tournament', 'initialRound', 'initialGame'));
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        try {
            $tournament = new Tournament();
            $this->validate($request, $tournament->rules(), $tournament->messages());
            $tournament->name = $request->get('name');
            $tournament->slug = $request->get('slug');
            $tournament->instagram_tag = $request->get('instagram_tag');
            $tournament->user_id = Auth::user()->id;
            $tournament->save();

            return redirect()->route('app.show', [$tournament->slug])->with('success_message', 'Congratulations! Your tournament has been created.');
        }
        catch (ValidationException $e) {
            return back()->withErrors()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $tournament = Tournament::findOrFail($id);
            $tournament->delete();
            return back()->with('success_message', 'Your tournament has been deleted.');
        }
        catch (\Exception $e) {
            return back()->withErrors();
        }
    }
}
