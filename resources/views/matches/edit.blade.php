@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Update match</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                {{ Form::model($match, ['route' => ['matches.update', $match->id], 'method' => 'PUT']) }}
                    <div class="row">
                        <div class="col-xs-6 form-group">
                            <label>Score: {{ $match->homeParticipant->name }} (home)</label>
                            {{ Form::text('home_score', null, ['class' => 'form-control']) }}
                        </div>
                        <div class="col-xs-6 form-group">
                            <label>Score: {{ $match->awayParticipant->name }} (away)</label>
                            {{ Form::text('away_score', null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="checkbox">
                                <label>
                                    {{ Form::checkbox('finished', 1) }}
                                    This game is finished.
                                </label>
                            </div>
                        </div>
                    </div>
                    {{ Form::submit('Update', ['class' => 'btn btn-lg btn-primary']) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection
