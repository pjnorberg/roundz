@extends('layouts.app')

@section('content')
    <div class="container">
        <input type="hidden" v-model="tournamentId" value="{{ $tournament->id }}">
        <div class="page-header">
            <h1>Edit tournament</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="well">
                    {{ Form::model($tournament, ['method' => 'PUT', 'route' => ['app.update', $tournament->id]]) }}
                        @include ('partials._form')
                    {{ Form::close() }}
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Participants
                        <span class="badge pull-right">{{ $tournament->participants()->count() }}</span>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <input type="text" class="invisible-input" placeholder="Type name and hit enter to add player">
                            </li>
                            <li class="list-group-item" v-for="participant in participants">
                                <span class="pull-right"><i class="fa fa-trash" aria-hidden="true"></i></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Matches
                        <span class="badge badge-primary pull-right">{{ $tournament->matches()->count() }}</span>
                    </div>
                    <div class="panel-body">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
