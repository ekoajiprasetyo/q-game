<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Matematika Dasar
        $mathTopic = Topic::where('name', 'Matematika Dasar')->first();
        if ($mathTopic) {
            $mathQuestions = [
                [
                    'question_text' => 'Berapa hasil dari 15 × 12?',
                    'options' => [
                        ['key' => 'A', 'text' => '170'],
                        ['key' => 'B', 'text' => '180'],
                        ['key' => 'C', 'text' => '190'],
                        ['key' => 'D', 'text' => '200'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Berapa hasil dari 256 ÷ 8?',
                    'options' => [
                        ['key' => 'A', 'text' => '28'],
                        ['key' => 'B', 'text' => '30'],
                        ['key' => 'C', 'text' => '32'],
                        ['key' => 'D', 'text' => '34'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Berapa hasil dari 7² + 5²?',
                    'options' => [
                        ['key' => 'A', 'text' => '64'],
                        ['key' => 'B', 'text' => '74'],
                        ['key' => 'C', 'text' => '84'],
                        ['key' => 'D', 'text' => '94'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'medium',
                ],
                [
                    'question_text' => 'Jika x + 15 = 42, berapa nilai x?',
                    'options' => [
                        ['key' => 'A', 'text' => '25'],
                        ['key' => 'B', 'text' => '27'],
                        ['key' => 'C', 'text' => '29'],
                        ['key' => 'D', 'text' => '31'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'medium',
                ],
                [
                    'question_text' => 'Berapa hasil dari √144?',
                    'options' => [
                        ['key' => 'A', 'text' => '10'],
                        ['key' => 'B', 'text' => '11'],
                        ['key' => 'C', 'text' => '12'],
                        ['key' => 'D', 'text' => '13'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Berapa 25% dari 80?',
                    'options' => [
                        ['key' => 'A', 'text' => '15'],
                        ['key' => 'B', 'text' => '20'],
                        ['key' => 'C', 'text' => '25'],
                        ['key' => 'D', 'text' => '30'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'medium',
                ],
                [
                    'question_text' => 'Berapa hasil dari 3³?',
                    'options' => [
                        ['key' => 'A', 'text' => '9'],
                        ['key' => 'B', 'text' => '18'],
                        ['key' => 'C', 'text' => '27'],
                        ['key' => 'D', 'text' => '36'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Berapakah KPK dari 6 dan 8?',
                    'options' => [
                        ['key' => 'A', 'text' => '12'],
                        ['key' => 'B', 'text' => '24'],
                        ['key' => 'C', 'text' => '36'],
                        ['key' => 'D', 'text' => '48'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'medium',
                ],
                [
                    'question_text' => 'Berapa hasil dari 1.5 × 4?',
                    'options' => [
                        ['key' => 'A', 'text' => '5'],
                        ['key' => 'B', 'text' => '6'],
                        ['key' => 'C', 'text' => '7'],
                        ['key' => 'D', 'text' => '8'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Jika segitiga memiliki alas 10 cm dan tinggi 6 cm, berapa luasnya?',
                    'options' => [
                        ['key' => 'A', 'text' => '25 cm²'],
                        ['key' => 'B', 'text' => '30 cm²'],
                        ['key' => 'C', 'text' => '35 cm²'],
                        ['key' => 'D', 'text' => '60 cm²'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'medium',
                ],
            ];

            foreach ($mathQuestions as $q) {
                Question::create([
                    'topic_id' => $mathTopic->id,
                    'question_text' => $q['question_text'],
                    'question_type' => 'multiple_choice',
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'difficulty' => $q['difficulty'],
                    'points' => $q['difficulty'] === 'easy' ? 10 : ($q['difficulty'] === 'medium' ? 15 : 20),
                    'time_limit' => 30,
                ]);
            }
        }

        // Pengetahuan Umum
        $generalTopic = Topic::where('name', 'Pengetahuan Umum')->first();
        if ($generalTopic) {
            $generalQuestions = [
                [
                    'question_text' => 'Apa ibukota negara Indonesia?',
                    'options' => [
                        ['key' => 'A', 'text' => 'Surabaya'],
                        ['key' => 'B', 'text' => 'Bandung'],
                        ['key' => 'C', 'text' => 'Jakarta'],
                        ['key' => 'D', 'text' => 'Yogyakarta'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Gunung tertinggi di Indonesia adalah?',
                    'options' => [
                        ['key' => 'A', 'text' => 'Gunung Merapi'],
                        ['key' => 'B', 'text' => 'Gunung Semeru'],
                        ['key' => 'C', 'text' => 'Puncak Jaya'],
                        ['key' => 'D', 'text' => 'Gunung Rinjani'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'medium',
                ],
                [
                    'question_text' => 'Siapa presiden pertama Indonesia?',
                    'options' => [
                        ['key' => 'A', 'text' => 'Soekarno'],
                        ['key' => 'B', 'text' => 'Soeharto'],
                        ['key' => 'C', 'text' => 'Habibie'],
                        ['key' => 'D', 'text' => 'Megawati'],
                    ],
                    'correct_answer' => 'A',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Berapa jumlah provinsi di Indonesia (2024)?',
                    'options' => [
                        ['key' => 'A', 'text' => '34'],
                        ['key' => 'B', 'text' => '36'],
                        ['key' => 'C', 'text' => '38'],
                        ['key' => 'D', 'text' => '40'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'medium',
                ],
                [
                    'question_text' => 'Hari kemerdekaan Indonesia diperingati pada tanggal?',
                    'options' => [
                        ['key' => 'A', 'text' => '17 Agustus'],
                        ['key' => 'B', 'text' => '1 Juni'],
                        ['key' => 'C', 'text' => '28 Oktober'],
                        ['key' => 'D', 'text' => '10 November'],
                    ],
                    'correct_answer' => 'A',
                    'difficulty' => 'easy',
                ],
            ];

            foreach ($generalQuestions as $q) {
                Question::create([
                    'topic_id' => $generalTopic->id,
                    'question_text' => $q['question_text'],
                    'question_type' => 'multiple_choice',
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'difficulty' => $q['difficulty'],
                    'points' => $q['difficulty'] === 'easy' ? 10 : ($q['difficulty'] === 'medium' ? 15 : 20),
                    'time_limit' => 30,
                ]);
            }
        }

        // IPA - Sains
        $scienceTopic = Topic::where('name', 'IPA - Sains')->first();
        if ($scienceTopic) {
            $scienceQuestions = [
                [
                    'question_text' => 'Apa rumus kimia air?',
                    'options' => [
                        ['key' => 'A', 'text' => 'H2O'],
                        ['key' => 'B', 'text' => 'CO2'],
                        ['key' => 'C', 'text' => 'O2'],
                        ['key' => 'D', 'text' => 'NaCl'],
                    ],
                    'correct_answer' => 'A',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Planet terbesar di tata surya adalah?',
                    'options' => [
                        ['key' => 'A', 'text' => 'Mars'],
                        ['key' => 'B', 'text' => 'Jupiter'],
                        ['key' => 'C', 'text' => 'Saturnus'],
                        ['key' => 'D', 'text' => 'Uranus'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Proses tumbuhan menghasilkan makanan dengan bantuan cahaya matahari disebut?',
                    'options' => [
                        ['key' => 'A', 'text' => 'Respirasi'],
                        ['key' => 'B', 'text' => 'Fotosintesis'],
                        ['key' => 'C', 'text' => 'Transpirasi'],
                        ['key' => 'D', 'text' => 'Fermentasi'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'Berapa kecepatan cahaya (dalam km/s)?',
                    'options' => [
                        ['key' => 'A', 'text' => '100.000 km/s'],
                        ['key' => 'B', 'text' => '200.000 km/s'],
                        ['key' => 'C', 'text' => '300.000 km/s'],
                        ['key' => 'D', 'text' => '400.000 km/s'],
                    ],
                    'correct_answer' => 'C',
                    'difficulty' => 'hard',
                ],
                [
                    'question_text' => 'Organ tubuh manusia yang berfungsi memompa darah adalah?',
                    'options' => [
                        ['key' => 'A', 'text' => 'Paru-paru'],
                        ['key' => 'B', 'text' => 'Jantung'],
                        ['key' => 'C', 'text' => 'Hati'],
                        ['key' => 'D', 'text' => 'Ginjal'],
                    ],
                    'correct_answer' => 'B',
                    'difficulty' => 'easy',
                ],
            ];

            foreach ($scienceQuestions as $q) {
                Question::create([
                    'topic_id' => $scienceTopic->id,
                    'question_text' => $q['question_text'],
                    'question_type' => 'multiple_choice',
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'difficulty' => $q['difficulty'],
                    'points' => $q['difficulty'] === 'easy' ? 10 : ($q['difficulty'] === 'medium' ? 15 : 20),
                    'time_limit' => 30,
                ]);
            }
        }
    }
}
