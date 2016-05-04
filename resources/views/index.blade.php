@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @foreach ($tournaments as $tournament)
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="{{ route('app.show', ['slug' => $tournament->slug]) }}">{{ $tournament->name }}</a>
                    <a href="{{ route('app.edit', ['id' => $tournament->id]) }}" class="pull-right"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                </div>
                <div class="panel-body">
                    {{ $tournament->participants()->count() }} deltagare
                    <p>
                    {{ Form::open(['route' => ['app.destroy', $tournament->id], 'method' => 'DELETE', 'class' => 'pull-right']) }}
                        <button class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    {{ Form::close() }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="well">
                <a href="{{ route('app.create') }}" class="btn btn-primary btn-lg">Create new tournament</a>
            </div>
        </div>
    </div>
</div>
@endsection
