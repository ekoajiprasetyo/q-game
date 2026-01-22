<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materialsData = [
            // Matematika Dasar
            'Matematika Dasar' => [
                ['name' => 'Penjumlahan', 'description' => 'Operasi penjumlahan bilangan', 'icon' => '➕', 'color' => '#FF9B50'],
                ['name' => 'Pengurangan', 'description' => 'Operasi pengurangan bilangan', 'icon' => '➖', 'color' => '#6EC6FF'],
                ['name' => 'Perkalian', 'description' => 'Operasi perkalian bilangan', 'icon' => '✖️', 'color' => '#B47EFF'],
                ['name' => 'Pembagian', 'description' => 'Operasi pembagian bilangan', 'icon' => '➗', 'color' => '#7DCEA0'],
            ],
            // Pengetahuan Umum
            'Pengetahuan Umum' => [
                ['name' => 'Geografi Indonesia', 'description' => 'Ibukota, provinsi, dan pulau di Indonesia', 'icon' => '🗺️', 'color' => '#FFD166'],
                ['name' => 'Tokoh Sejarah', 'description' => 'Pahlawan dan tokoh penting Indonesia', 'icon' => '👤', 'color' => '#FF8A8A'],
                ['name' => 'Hari Besar Nasional', 'description' => 'Peringatan hari besar di Indonesia', 'icon' => '🎌', 'color' => '#FF9ECD'],
            ],
            // Bahasa Indonesia
            'Bahasa Indonesia' => [
                ['name' => 'Sinonim & Antonim', 'description' => 'Persamaan dan lawan kata', 'icon' => '📝', 'color' => '#6EC6FF'],
                ['name' => 'Peribahasa', 'description' => 'Peribahasa dan maknanya', 'icon' => '📜', 'color' => '#B47EFF'],
                ['name' => 'Tata Bahasa', 'description' => 'Struktur kalimat dan EYD', 'icon' => '✍️', 'color' => '#7DCEA0'],
            ],
            // IPA - Sains
            'IPA - Sains' => [
                ['name' => 'Sistem Tata Surya', 'description' => 'Planet, bintang, dan luar angkasa', 'icon' => '🪐', 'color' => '#FFD166'],
                ['name' => 'Tubuh Manusia', 'description' => 'Organ dan sistem dalam tubuh', 'icon' => '🫀', 'color' => '#FF8A8A'],
                ['name' => 'Hewan & Tumbuhan', 'description' => 'Klasifikasi makhluk hidup', 'icon' => '🌿', 'color' => '#7DCEA0'],
            ],
        ];

        foreach ($materialsData as $topicName => $materials) {
            $topic = Topic::where('name', $topicName)->first();
            if ($topic) {
                foreach ($materials as $materialData) {
                    Material::create([
                        'topic_id' => $topic->id,
                        'name' => $materialData['name'],
                        'description' => $materialData['description'],
                        'icon' => $materialData['icon'],
                        'color' => $materialData['color'],
                    ]);
                }
            }
        }
    }
}
