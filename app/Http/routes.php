<?php

Route::auth();

// Tournament overview:
Route::get('/', [
    'as' => 'app.home',
    'uses' => 'AppController@index',
]);

Route::resource('app', 'AppController');
Route::resource('tournaments', 'TournamentController');
Route::resource('participants', 'ParticipantsController');
Route::resource('matches', 'MatchesController');

Route::delete('/tournaments/{tournamentId}/delete-matches', [
    'as' => 'tournament.deleteMatches',
    'uses' => 'TournamentController@deleteMatches'
]);

// Show tournament:
Route::get('/{slug}', [
    'as' => 'app.show',
    'uses' => 'AppController@show',
]);