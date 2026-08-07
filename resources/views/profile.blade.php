@extends('app')

@section('content')

<a class="top-right-corner red-btn" href="{{ route('home') }}">Back ></a>

<div style="margin-top:100px">
    <div class="profile-header">
        <p class="title profile-name">{{ auth()->user()->username }}</p>
        <p class="title profile-email">{{ auth()->user()->email }}</p>
    </div>

    <div class="profile-header">
        <p class="title profile-xp">{{ auth()->user()->xp }} XP</p>
        <p class="title profile-email">{{ auth()->user()->rank }}</p>

    </div>

    <table class="stats-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Correct</th>
                <th>Total</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
            <tr>
                <td>{{ $category['label'] }}</td>
                <td>{{ $category['correct'] }}</td>
                <td>{{ $category['total'] }}</td>
                <td>{{ $category['percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>



@endsection
