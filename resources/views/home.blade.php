@extends('app')

@section('content')

@auth
<a class="top-left-corner blue-btn" href="{{ route('profile') }}">Profile</a>
@endauth

@guest
<a class="top-left-corner blue-btn" href="{{ route('login') }}">Login</a>
@endguest

<a class="top-right-corner blue-btn" href="{{ route('leaderboard') }}">Leaderboard</a>

@auth
<form class="bottom-right-corner" action="{{ route('logout') }}" method="POST">
    @csrf
    <button class="red-btn" style="cursor: pointer;" type="submit">Logout</button>
</form>
@endauth

<div class="main-img">
    <img src="{{ asset('images/mister_quiz.png') }}" alt="">
    <p class="title">Mister Quiz</p>

    @if (session('status'))
    <div class="center error-msg mb4">
        {{ session('status') }}
    </div>
    @endif

    <a style="margin-bottom:20px" class="green-btn center" href="{{ route('quiz') }}">Start Quiz</a>
</div>

@endsection
