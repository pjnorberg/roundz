@extends('layouts.public')

@section('content')
    <h1 class="tournamentHeader">{{ $tournament->name }}</h1>
    <div id="tournament">
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/svg.js/2.3.0/svg.min.js"></script>
    <script>
        var tournament = {
            id: {{ $tournament->id }},
            matches: [
            @foreach ($tournament->playoffMatches as $match)
                {
                    id: {{ $match->id }},
                    game: {{ $initialGame++ }},
                    round: {{ $match->round }},
                    home_team: '{{ $match->homeParticipant ? $match->homeParticipant->name : '' }}',
                    away_team: '{{ $match->awayParticipant ? $match->awayParticipant->name : '' }}',
                    home_score: '{{ $match->home_score }}',
                    away_score: '{{ $match->away_score }}',
                    finished: {{ $match->finished }}
                }
                {{ $match->id != $tournament->playoffMatches->last()->id ? ',' : '' }}
            @endforeach
            ]
        };

        // Draw some SVG:
        var box = [];
        var canvas = SVG('tournament').size('100%', '100%');

        // Get width by diving by no. of rounds:
        var width = 100 / 4;

        // Get height by dividing by no. of games in first round:
        var height = 100 / 8;

        canvas.rect(width+"%", height+"%").attr({ fill: '#000' });
    </script>
@endsection
