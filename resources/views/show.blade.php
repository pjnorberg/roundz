@extends('layouts.public')

@section('content')
    <span class="tournamentHeader">
        <h1>{{ $tournament->name }}</h1>
        <small class="subheader">Playoff</small>
    </span>
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
                                            <span class="home-team-score score">{{ $match->home_score }}</span>
                                        </div>
                                        <div class="team away-team{{ $match->finished && $match->awayParticipant->id == $match->winner()->id ? ' winner-team' : '' }}">
                                            <span class="away-team-name name">{{ $match->awayParticipant ? $match->awayParticipant->name : '' }}</span>
                                            <span class="away-team-score score">{{ $match->away_score }}</span>
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
