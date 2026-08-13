<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MisterQuizTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testGuestAndAuthenticatedMenusMatchTheAudit()
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Login')
            ->assertSee('Leaderboard')
            ->assertSee('Start Quiz')
            ->assertDontSee('Logout');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee('Leaderboard')
            ->assertSee('Start Quiz')
            ->assertSee('Logout');
    }

    public function testRegistrationAndLoginValidationWork()
    {
        $this->post(route('register'), [
            'username' => 'new-player',
            'email' => 'new-player@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'new-player',
            'email' => 'new-player@example.com',
        ]);

        $this->post(route('logout'));
        $this->assertGuest();

        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $this->get(route('quiz'))->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertSessionHas('status');

        $this->assertGuest();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function testGuestCannotStartAQuiz()
    {
        $this->get(route('quiz'))->assertRedirect(route('login'));
    }

    public function testUnfinishedQuizKeepsTheSameQuestions()
    {
        $user = User::factory()->create();

        $firstResponse = $this->actingAs($user)->get(route('quiz'));
        $firstResponse->assertOk();

        $firstQuestions = $firstResponse->viewData('questions');
        $firstIds = $firstQuestions->pluck('id')->sort()->values()->all();

        foreach (['Art', 'History', 'Geography', 'Science', 'Sports'] as $category) {
            $this->assertGreaterThanOrEqual(1, $firstQuestions->where('category', $category)->count());
        }

        $this->get(route('home'))->assertOk();

        $secondResponse = $this->get(route('quiz'));
        $secondResponse->assertOk();

        $secondIds = $secondResponse->viewData('questions')->pluck('id')->sort()->values()->all();

        $this->assertSame($firstIds, $secondIds);
        $this->assertSame(1, Quiz::where('user_id', $user->id)->where('completed', false)->count());
    }

    public function testQuizCannotBeSubmittedWithMissingAnswers()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('quiz'));

        $quiz = Quiz::where('user_id', $user->id)->where('completed', false)->firstOrFail();

        $this->post(route('quiz.results', $quiz), ['answers' => []])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'completed' => false,
        ]);
        $this->assertSame(0, $user->fresh()->xp);
    }

    public function testSuccessfulSubmitRedirectsToPersistentResultsAndUpdatesStats()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('quiz'));

        $quiz = Quiz::where('user_id', $user->id)->where('completed', false)->firstOrFail();
        $questions = $quiz->getQuestions();
        $answers = $this->correctAnswersFor($quiz);
        $expectedXp = (int) $questions->sum('xp');
        $questionCount = $questions->count();

        $this->post(route('quiz.results', $quiz), ['answers' => $answers])
            ->assertRedirect(route('quiz.results.show', $quiz));

        $freshUser = $user->fresh();
        $this->assertSame($expectedXp, $freshUser->xp);

        foreach ($questions->groupBy('category') as $category => $categoryQuestions) {
            $count = $categoryQuestions->count();
            $this->assertSame($count . '/' . $count, $freshUser->{strtolower($category)});
        }

        $freshQuiz = $quiz->fresh();
        $this->assertTrue($freshQuiz->completed);
        $this->assertSame($expectedXp, $freshQuiz->xp_earned);
        $this->assertSame($questionCount, $freshQuiz->results['results']['overall']);

        $resultsUrl = route('quiz.results.show', $quiz);

        $this->get($resultsUrl)
            ->assertOk()
            ->assertSee($questionCount . ' / ' . $questionCount)
            ->assertSee('+' . $expectedXp . ' XP');

        $this->get($resultsUrl)
            ->assertOk()
            ->assertSee($questionCount . ' / ' . $questionCount);
    }

    public function testCompletedQuizCannotAwardXpTwice()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('quiz'));

        $quiz = Quiz::where('user_id', $user->id)->where('completed', false)->firstOrFail();
        $answers = $this->correctAnswersFor($quiz);

        $this->post(route('quiz.results', $quiz), ['answers' => $answers])
            ->assertRedirect(route('quiz.results.show', $quiz));

        $xpAfterFirstSubmit = $user->fresh()->xp;

        $this->post(route('quiz.results', $quiz), ['answers' => $answers])
            ->assertStatus(409);

        $this->assertSame($xpAfterFirstSubmit, $user->fresh()->xp);
    }

    public function testLogoutIsPostOnly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/logout')->assertStatus(405);
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function testLeaderboardContainsOnlyTopTenPlayersInXpOrder()
    {
        for ($i = 1; $i <= 12; $i++) {
            User::factory()->create([
                'username' => 'player-' . $i,
                'xp' => $i * 100,
            ]);
        }

        $response = $this->get(route('leaderboard'));
        $response->assertOk();

        $users = $response->viewData('users');

        $this->assertCount(10, $users);
        $this->assertSame(
            [1200, 1100, 1000, 900, 800, 700, 600, 500, 400, 300],
            $users->pluck('xp')->all()
        );
    }

    private function correctAnswersFor(Quiz $quiz): array
    {
        $answers = [];

        foreach ($quiz->getQuestions() as $question) {
            $correctAnswer = $question->answers->first(function ($answer) {
                return (bool) $answer->correct;
            });

            $this->assertNotNull($correctAnswer);
            $answers[$question->id] = $correctAnswer->id;
        }

        return $answers;
    }
}
