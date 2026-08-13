<?php

namespace App\Http\Controllers\Questions;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Question_Quiz;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    private const CATEGORIES = ['Art', 'History', 'Geography', 'Science', 'Sports'];
    private const QUESTIONS_PER_CATEGORY = 2;

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

        $questions = $quiz->getQuestions();

        return view('questions.list', [
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

    public function results(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->user_id === $request->user()->id, 403);

        if ($quiz->completed) {
            return redirect()->route('quiz.results.show', $quiz);
        }

        $questions = $quiz->getQuestions();
        $submittedAnswers = $request->input('answers', []);

        if (count($submittedAnswers) !== $questions->count()) {
            return back()->with('status', 'Please answer every question before submitting the quiz.');
        }

        $results = ['overall' => 0];
        $totals = [];

        foreach (self::CATEGORIES as $category) {
            $key = strtolower($category);
            $results[$key] = 0;
            $totals[$key] = 0;
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

        $submitted = DB::transaction(function () use ($request, $quiz, $results, $totals, $xp) {
            $claimed = Quiz::whereKey($quiz->id)
                ->where('user_id', $request->user()->id)
                ->where('completed', false)
                ->update([
                    'completed' => true,
                    'results' => json_encode([
                        'results' => $results,
                        'totals' => $totals,
                    ], JSON_THROW_ON_ERROR),
                    'xp_earned' => $xp,
                    'updated_at' => now(),
                ]);

            if ($claimed !== 1) {
                return false;
            }

            $user = User::whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user->xp += $xp;

            foreach ($results as $key => $value) {
                if ($key === 'overall') {
                    continue;
                }

                [$correct, $total] = explode('/', $user->{$key});
                $user->{$key} = ((int) $correct + $value) . '/' . ((int) $total + $totals[$key]);
            }

            $user->save();

            return true;
        });

        return redirect()->route('quiz.results.show', $quiz);
    }

    public function showResults(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->user_id === $request->user()->id, 403);
        abort_unless($quiz->completed && is_array($quiz->results), 404);

        return view('questions.results', [
            'results' => $quiz->results['results'],
            'totals' => $quiz->results['totals'],
            'xp' => $quiz->xp_earned,
        ]);
    }
}
