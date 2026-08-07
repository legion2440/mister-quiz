<?php

namespace App\Http\Controllers\Questions;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Question_Quiz;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    private const CATEGORIES = ['Art', 'History', 'Geography', 'Science', 'Sports'];
    private const QUESTIONS_PER_CATEGORY = 4;

    public function index(Request $request)
    {
        $quiz = Quiz::where('user_id', $request->user()->id)
            ->where('completed', false)
            ->latest()
            ->first();

        if (! $quiz) {
            $quiz = Quiz::create([
                'completed' => false,
                'user_id' => $request->user()->id,
            ]);

            foreach (self::CATEGORIES as $category) {
                $questions = Question::where('category', $category)
                    ->inRandomOrder()
                    ->limit(self::QUESTIONS_PER_CATEGORY)
                    ->get();

                if ($questions->isEmpty()) {
                    $quiz->delete();

                    return redirect()
                        ->route('home')
                        ->with('status', 'The quiz cannot start until every category has at least one question.');
                }

                foreach ($questions as $question) {
                    Question_Quiz::create([
                        'question_id' => $question->id,
                        'quizzes_id' => $quiz->id,
                    ]);
                }
            }
        }

        $questions = $quiz->getQuestions()->shuffle();

        return view('questions.list', [
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

    public function results(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->user_id === $request->user()->id && ! $quiz->completed, 403);

        $questions = $quiz->getQuestions();
        $submittedAnswers = $request->input('answers', []);

        if (count($submittedAnswers) !== $questions->count()) {
            return back()->with('status', 'Please answer every question before submitting the quiz.');
        }

        $results = ['overall' => 0];
        foreach (self::CATEGORIES as $category) {
            $results[strtolower($category)] = 0;
        }

        $totals = [];
        foreach (self::CATEGORIES as $category) {
            $totals[strtolower($category)] = 0;
        }

        $xp = 0;

        foreach ($questions as $question) {
            if (! isset($submittedAnswers[$question->id])) {
                return back()->with('status', 'Please answer every question before submitting the quiz.');
            }

            $answer = Answer::where('id', $submittedAnswers[$question->id])
                ->where('question_id', $question->id)
                ->first();

            if (! $answer) {
                return back()->with('status', 'One of the submitted answers is invalid.');
            }

            $categoryKey = strtolower($question->category);
            $totals[$categoryKey]++;

            if ($answer->correct) {
                $results['overall']++;
                $results[$categoryKey]++;
                $xp += $question->xp;
            }
        }

        $user = Auth::user();
        $user->xp += $xp;

        foreach ($results as $key => $value) {
            if ($key != 'overall') {
                [$correct, $total] = explode('/', $user->{$key});
                $user->{$key} = ((int) $correct + $value) . '/' . ((int) $total + $totals[$key]);
            }
        }

        $quiz->completed = true;
        $user->save();
        $quiz->save();

        return view('questions.results', [
            'results' => $results,
            'totals' => $totals,
            'xp' => $xp,
        ]);
    }
}
