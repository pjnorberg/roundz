<?php

Route::auth();

// Tournament overview:
Route::get('/', [
    'as' => 'app.index',
    'uses' => 'AppController@index',
]);

Route::resource('app', 'AppController');
Route::resource('api', 'ApiController');

// Show tournament:
Route::get('/{slug}', [
    'as' => 'app.show',
    'uses' => 'AppController@show',
]);