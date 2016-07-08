<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials._header')
</head>
<body id="app">

    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    Round Z
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li><a href="{{ url('/register') }}">Register</a></li>
                    @else
                        <li><a href="{{ url('/logout') }}">Logout &nbsp; <i class="fa fa-btn fa-sign-out"></i></a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    @if (Session::has('success_message'))
        <div class="notice notice-success">{{ Session::get('success_message') }}</div>
    @elseif (Session::has('error_message'))
        <div class="notice notice-error">{{ Session::get('error_message') }}</div>
    @elseif (count($errors))
        <div class="notice notice-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @yield('content')

    <!-- JavaScripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <script src="{{ elixir('js/all.js') }}"></script>
    @yield('scriptFooter')
</body>
</html>
