<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Criterion;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $judges = collect(range(1, 20))->map(
            fn (int $i) => User::firstOrCreate(
                ['username' => "juri{$i}"],
                [
                    'name' => "Juri {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'judge',
                ]
            )
        );

        if (Competition::count() > 0) {
            return;
        }

        [$osis, $pramuka] = collect(['Deville OSIS', 'Deville Pramuka'])->map(function (string $name) use ($judges) {
            $competition = Competition::create([
                'name' => $name,
                'start_time' => now()->subHour(),
                'end_time' => now()->addHours(6),
            ]);

            $criteriaDefinitions = [
                [
                    'name' => 'Kerapian',
                    'description' => 'Penilaian keseragaman seragam, kelengkapan atribut, dan kerapian barisan peserta.',
                    'sub_items' => [
                        ['name' => 'Keseragaman Seragam & Atribut', 'min_score' => 0, 'max_score' => 35],
                        ['name' => 'Kerapian Tata Rias & Rambut/Jilbab', 'min_score' => 0, 'max_score' => 35],
                        ['name' => 'Kelurusan & Kerapian Barisan', 'min_score' => 0, 'max_score' => 30],
                    ],
                ],
                [
                    'name' => 'Kekompakan',
                    'description' => 'Penilaian keselarasan derap langkah, keserasian tempo gerakan, dan kekompakan formasi regu.',
                    'sub_items' => [
                        ['name' => 'Keselarasan Derap Langkah & Tempo', 'min_score' => 0, 'max_score' => 40],
                        ['name' => 'Keseragaman Ayunan Tangan & Sikap', 'min_score' => 0, 'max_score' => 30],
                        ['name' => 'Penguasaan Aba-aba Pemimpin Regu', 'min_score' => 0, 'max_score' => 30],
                    ],
                ],
                [
                    'name' => 'Kreativitas',
                    'description' => 'Penilaian variasi gerakan formasi, atraksi visual, serta semangat dan artikulasi yel-yel.',
                    'sub_items' => [],
                ],
            ];

            foreach ($criteriaDefinitions as $def) {
                $hasItems = ! empty($def['sub_items']);
                $minScore = $hasItems ? (int) collect($def['sub_items'])->sum('min_score') : 0;
                $maxScore = $hasItems ? (int) collect($def['sub_items'])->sum('max_score') : 100;

                $criterion = Criterion::create([
                    'competition_id' => $competition->id,
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'school_types' => ['SD', 'SMP', 'SMA', 'Umum'],
                    'min_score' => $minScore,
                    'max_score' => $maxScore,
                ]);

                if ($hasItems) {
                    foreach ($def['sub_items'] as $order => $subItemData) {
                        $criterion->items()->create([
                            'name' => $subItemData['name'],
                            'min_score' => $subItemData['min_score'],
                            'max_score' => $subItemData['max_score'],
                            'order' => $order,
                        ]);
                    }
                }

                $rows = [];
                foreach ($criterion->school_types as $type) {
                    foreach ($judges as $judge) {
                        $rows[] = ['user_id' => $judge->id, 'school_type' => $type];
                    }
                }
                $criterion->judges()->newPivotStatement()->insert(array_map(
                    fn (array $row) => $row + ['criterion_id' => $criterion->id, 'created_at' => now(), 'updated_at' => now()],
                    $rows
                ));
            }

            return $competition;
        })->all();

        // Deville OSIS: didaftarkan via slot massal, identitas belum disinkronkan.
        foreach (['SD' => 10, 'SMP' => 10, 'SMA' => 10, 'Umum' => 10] as $schoolType => $count) {
            for ($seq = 1; $seq <= $count; $seq++) {
                Registration::create([
                    'competition_id' => $osis->id,
                    'school_type' => $schoolType,
                    'npp' => "{$schoolType}-{$seq}",
                    'label' => "{$schoolType} Peserta {$seq}",
                ]);
            }
        }

        // Deville Pramuka: identitas sudah diketahui saat registrasi (alur lama).
        $prefixes = [
            'SD' => ['SDN', 1000],
            'SMP' => ['SMPN', 2000],
            'SMA' => ['SMAN', 3000],
            'Umum' => ['Instansi/Umum', 4000],
        ];

        foreach ($prefixes as $schoolType => [$label, $nppBase]) {
            for ($i = 1; $i <= 10; $i++) {
                $participant = Participant::create([
                    'school_name' => "{$label} {$i} Merdeka",
                    'school_type' => $schoolType,
                ]);

                Registration::create([
                    'competition_id' => $pramuka->id,
                    'participant_id' => $participant->id,
                    'school_type' => $schoolType,
                    'npp' => (string) ($nppBase + $i),
                ]);
            }
        }
    }
}
