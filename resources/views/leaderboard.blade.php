@extends('app')

@section('content')

<a class="top-right-corner blue-btn" href="{{ route('home') }}">Home</a>

<div class="content">
    <p class="title">Leaderboard</p>

    <table class="stats-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>XP</th>
                <th>Total Correct</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $index => $user)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->xp }}</td>
                <td>{{ $user->totalCorrectAnswers() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No players yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
