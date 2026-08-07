@extends('app')

@section('content')

<a class="top-right-corner blue-btn" href="{{ route('home') }}">Home</a>

<form action="{{ route('quiz.results', $quiz) }}" method="post">
    @csrf

    @if (session('status'))
    <div class="center error-msg mb4">
        {{ session('status') }}
    </div>
    @endif

    @foreach ($questions as $question)
    <x-question :question="$question" />
    @endforeach

    <button type="submit" class="center green-btn">Submit</button>
</form>


@endsection
