@extends('layouts.public')

@section('content')
    <span class="tournamentHeader">
        <h1>{{ $tournament->name }}</h1>
    </span>
    <div id="qualifying">
        <div class="qualifier-wrapper">
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-center" title="Games played">G</th>
                    <th class="text-center">+/-</th>
                    <th class="text-center" title="Points">P</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($participants as $row => $participant)
                    @if ($row == $tournament->playoff_size)
                        <tr class="cutoff">
                    @else
                        <tr>
                            @endif
                            <td>{{ $participant->name }}</td>
                            <td class="text-center">{{ $participant->points }}</td>
                            <td class="text-center">{{ $participant->diff }}</td>
                            <td class="text-center">{{ $participant->games_played }}</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div id="tournament" class="clearfix">
        @foreach ($rounds as $round)
            <div class="round" style="width: {{ 100 / count($rounds) }}%;">
                <div class="matches-wrapper{{ $round > 1 ? ' push' : '' }}">
                    @foreach ($tournament->playoffMatches as $match)
                        @if ($match->round == $round)
                            {!! $match->finished ? '' : '<a href="' . route('matches.edit', [$match->id]) . '">' !!}
                                <div class="match-wrapper" style="height: 12.5%;">
                                    <div class="match clearfix{{ $match->hasTeams() ? '' : ' waiting' }}">
                                        <div class="team home-team{{ $match->finished && $match->homeParticipant->id == $match->winner()->id ? ' winner-team' : '' }}">
                                            <span class="home-team-name name">{{ $match->homeParticipant ? $match->homeParticipant->name : '' }}</span>
                                            <span class="home-team-score score">
                                                {{ $match->home_score }}
                                            </span>
                                        </div>
                                        <div class="team away-team{{ $match->finished && $match->awayParticipant->id == $match->winner()->id ? ' winner-team' : '' }}">
                                            <span class="away-team-name name">{{ $match->awayParticipant ? $match->awayParticipant->name : '' }}</span>
                                            <span class="away-team-score score">
                                                {{ $match->away_score }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            {!! $match->finished ? '' : '</a>' !!}
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection
