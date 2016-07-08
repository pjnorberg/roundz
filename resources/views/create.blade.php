@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Create tournament</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="well">
                    {{ Form::open(['method' => 'POST', 'route' => ['tournaments.store']]) }}
                        @include ('partials._form')
                    {{ Form::close() }}
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
