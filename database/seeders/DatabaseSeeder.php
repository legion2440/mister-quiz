<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $questions = [
            'Art' => [
                ['Which artist painted the Mona Lisa?', 100, ['Leonardo da Vinci', 'Pablo Picasso', 'Vincent van Gogh', 'Claude Monet']],
                ['Which color is made by mixing red and blue?', 80, ['Purple', 'Green', 'Orange', 'Yellow']],
                ['What is origami?', 80, ['Paper folding', 'Stone carving', 'Glass painting', 'Metal casting']],
                ['Which museum is home to the Mona Lisa?', 120, ['The Louvre', 'The Prado', 'The Uffizi', 'The Met']],
            ],
            'History' => [
                ['Who was the first president of the United States?', 100, ['George Washington', 'Abraham Lincoln', 'Thomas Jefferson', 'John Adams']],
                ['In which year did World War II end?', 120, ['1945', '1939', '1918', '1961']],
                ['The pyramids of Giza were built in which country?', 80, ['Egypt', 'Greece', 'Mexico', 'India']],
                ['Who was known as the Maid of Orleans?', 100, ['Joan of Arc', 'Cleopatra', 'Marie Curie', 'Queen Victoria']],
            ],
            'Geography' => [
                ['What is the capital of France?', 80, ['Paris', 'Lyon', 'Berlin', 'Madrid']],
                ['Which is the largest ocean on Earth?', 100, ['Pacific Ocean', 'Atlantic Ocean', 'Indian Ocean', 'Arctic Ocean']],
                ['Mount Everest is located in which mountain range?', 120, ['Himalayas', 'Andes', 'Alps', 'Rockies']],
                ['Which country has the city of Tokyo as its capital?', 80, ['Japan', 'China', 'South Korea', 'Thailand']],
            ],
            'Science' => [
                ['What planet is known as the Red Planet?', 80, ['Mars', 'Venus', 'Jupiter', 'Mercury']],
                ['What gas do plants absorb from the atmosphere?', 100, ['Carbon dioxide', 'Oxygen', 'Nitrogen', 'Helium']],
                ['How many bones are in the adult human body?', 120, ['206', '150', '300', '98']],
                ['What is H2O commonly known as?', 80, ['Water', 'Salt', 'Hydrogen', 'Oxygen']],
            ],
            'Sports' => [
                ['How many players are on a soccer team on the field?', 80, ['11', '7', '9', '12']],
                ['Which sport uses a racket and shuttlecock?', 100, ['Badminton', 'Tennis', 'Cricket', 'Squash']],
                ['The Olympic Games are held every how many years?', 80, ['4', '2', '3', '5']],
                ['In basketball, how many points is a free throw worth?', 80, ['1', '2', '3', '4']],
            ],
        ];

        foreach ($questions as $category => $items) {
            foreach ($items as [$text, $xp, $answers]) {
                $questionId = DB::table('question')->insertGetId([
                    'question' => $text,
                    'xp' => $xp,
                    'category' => $category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $answerRows = [];
                foreach ($answers as $index => $answer) {
                    $answerRows[] = [
                        'answer' => $answer,
                        'correct' => $index === 0,
                    ];
                }

                shuffle($answerRows);

                foreach ($answerRows as $answerRow) {
                    DB::table('answer')->insert([
                        'answer' => $answerRow['answer'],
                        'correct' => $answerRow['correct'],
                        'question_id' => $questionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
