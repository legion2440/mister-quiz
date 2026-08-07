@props(['question'=>$question])

<div class="mb4">
    <p class="center title question-title">{{ $question->question }}</p>
    <p class="center question-meta">{{ $question->category }} | {{ $question->xp }} XP</p>

    <div class="checkboxes-wrapper" class="center">
        @foreach ($question->answers as $answer)
        <div class="checkbox">
            <label>
                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" required>
                <span>{{ $answer->answer }}</span>
            </label>
        </div>
        @endforeach
    </div>

    <div class="center line"></div>
</div>
