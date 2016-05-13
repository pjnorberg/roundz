<?php

Route::auth();

// Tournament overview:
Route::get('/', [
    'as' => 'app.index',
    'uses' => 'AppController@index',
]);

Route::resource('app', 'AppController');
Route::resource('participants', 'ParticipantsController');
Route::resource('matches', 'MatchesController');

Route::delete('/app/{tournamentId}/delete-matches', [
    'as' => 'tournament.deleteMatches',
    'uses' => 'AppController@deleteMatches'
]);

// Show tournament:
Route::get('/{slug}', [
    'as' => 'app.show',
    'uses' => 'AppController@show',
]);