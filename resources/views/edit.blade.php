@extends('layouts.app')

@section('content')
    <div id="v-edit" class="container">
        <script>
            // Output all data needed for this view in a global var:
            var tournament = {
                id: {{ $tournament->id }},
                instagram_tag: '{{ $tournament->instagram_tag }}',
                token: '{{ csrf_token() }}',
                participants: [
                    @foreach ($tournament->participants as $participant)
                    {
                        id: {{ $participant->id }},
                        name: '{{ $participant->name }}',
                        points: 0,
                        qualified: 0,
                    }
                    {{ $participant->id != $tournament->participants->last()->id ? ',' : '' }}
                    @endforeach
                ]
            };
        </script>
        <div class="page-header">
            <h1>Manage tournament</h1>
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
                        <span class="badge pull-right">@{{ tournament.participants.length }}</span>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <input type="text" class="invisible-input" v-on:keyup.enter="addParticipant" placeholder="Type name and hit enter to add player">
                            </li>
                            <li class="list-group-item" v-for="participant in tournament.participants">
                                @{{{ participant.name }}}
                                <span class="pull-right clickable" v-on:click="deleteParticipant($index)"><i class="fa fa-trash" aria-hidden="true"></i></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                @if ($tournament->matches()->count() > 0)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Matches
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Game #</th>
                                        <th>Type</th>
                                        <th>Home team</th>
                                        <th>Away team</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tournament->matches as $match)
                                    <tr>
                                        <td>{{ $matchCount++ }}</td>
                                        <td>{{ $match->type() }}</td>
                                        <td>
                                            {{ $match->homeParticipant ? $match->homeParticipant->name : $match->potentialParticipants('home') }}
                                        </td>
                                        <td>
                                            {{ $match->awayParticipant ? $match->awayParticipant->name : $match->potentialParticipants('away') }}
                                        </td>
                                        <td>{{ $match->home_score }} &mdash; {{ $match->away_score }}</td>
                                        <td>{{ $match->getStatus() }}</td>
                                        <td>
                                            @if ( ! $match->finished)
                                            <a href="{{ route('matches.edit', [$match->id]) }}">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ Form::open(['route' => ['tournament.deleteMatches', $tournament->id], 'method' => 'DELETE']) }}
                                {{ Form::submit('Delete all matches', ['class' => 'btn btn-danger']) }}
                            {{ Form::close() }}
                        </div>
                    </div>
                @else
                    <div class="panel panel-default">
                    <div class="panel-heading">
                        Suggested matches
                        <span class="badge badge-primary pull-right">@{{ qualifyingMatches.length + playoffMatches.length }}</span>
                    </div>
                    <div class="panel-body">
                        <template v-if="actionStatus" class="statusbar text-center text-danger">
                            @{{{ actionStatus }}}
                        </template>
                        <div>
                            <button type="button" class="btn btn-primary" v-on:click="generateMatches">Generate matches</button>
                            <button type="button" class="btn btn-danger" v-on:click="persistMatches">Create tournament</button>
                        </div>
                        <hr>
                        <template v-if="qualifyingMatches.length > 0">
                            <h3>Qualifying round</h3>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Game #</th>
                                    <th>Home team</th>
                                    <th>Away team</th>
                                    <th>Score</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="match in qualifyingMatches">
                                    <td>@{{ match.id + 1 }}</td>
                                    <td>@{{{ match._home_name }}}</td>
                                    <td>@{{{ match._away_name }}}</td>
                                    <td>@{{ match.home_score }} &mdash; @{{ match.away_score }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </template>
                        <template v-if="playoffMatches.length > 0">
                            <h3>Playoff</h3>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Game #</th>
                                        <th>Round</th>
                                        <th>Home team</th>
                                        <th>Away team</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="match in playoffMatches">
                                        <td>@{{ match.id + 1 }}</td>
                                        <td>@{{ match.round }}</td>
                                        <td>@{{{ match._home_name }}}</td>
                                        <td>@{{{ match._away_name }}}</td>
                                        <td>@{{ match.home_score }} &mdash; @{{ match.away_score }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection