<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            [
                'name' => 'Matematika Dasar',
                'description' => 'Operasi hitung dasar: penjumlahan, pengurangan, perkalian, pembagian',
                'subject' => 'Matematika',
                'icon' => '🔢',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Pengetahuan Umum',
                'description' => 'Fakta-fakta umum tentang dunia, geografi, dan sejarah',
                'subject' => 'IPS',
                'icon' => '🌍',
                'color' => '#10B981',
            ],
            [
                'name' => 'Bahasa Indonesia',
                'description' => 'Kosakata, tata bahasa, dan sastra Indonesia',
                'subject' => 'Bahasa Indonesia',
                'icon' => '📚',
                'color' => '#EF4444',
            ],
            [
                'name' => 'IPA - Sains',
                'description' => 'Ilmu pengetahuan alam: fisika, kimia, biologi',
                'subject' => 'IPA',
                'icon' => '🔬',
                'color' => '#8B5CF6',
            ],
        ];

        foreach ($topics as $topic) {
            Topic::create($topic);
        }
    }
}
