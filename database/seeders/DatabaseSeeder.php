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

        if (! Competition::where('name', 'Deville OSIS')->exists()) {
            $this->seedDevilleCompetitions($judges);
        }

        $this->seedDrumbandCompetition($judges);
        $this->seedKarnavalCompetition($judges);
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $judges
     */
    protected function seedDevilleCompetitions($judges): void
    {
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

        // Deville Pramuka: identitas sudah diketahui saat registrasi.
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

    /**
     * @param \Illuminate\Support\Collection<int, User> $judges
     */
    protected function seedDrumbandCompetition($judges): void
    {
        if (Competition::where('name', 'Lomba Drumband / Marching Band')->exists()) {
            return;
        }

        $competition = Competition::create([
            'name' => 'Lomba Drumband / Marching Band',
            'categories' => ['Marching Band', 'Drum Band'],
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(6),
        ]);

        $criteriaDefinitions = [
            [
                'name' => 'Music Performance',
                'description' => 'Penilaian kualitas suara keseluruhan, artikulasi, teknik dinamika, serta balance dan blend musik.',
                'sub_items' => [
                    ['name' => 'Kualitas Suara Keseluruhan', 'min_score' => 0, 'max_score' => 40],
                    ['name' => 'Artikulasi dan Teknik & Dinamika', 'min_score' => 0, 'max_score' => 40],
                    ['name' => 'Balance and Blend', 'min_score' => 0, 'max_score' => 20],
                ],
            ],
            [
                'name' => 'Visual Performance',
                'description' => 'Penilaian keseragaman dan sinkronisasi gerak, kerapian formasi dan interval, serta control of movement.',
                'sub_items' => [
                    ['name' => 'Keseragaman dan Sinkronisasi Gerak', 'min_score' => 0, 'max_score' => 40],
                    ['name' => 'Kerapian Formasi & Interval', 'min_score' => 0, 'max_score' => 40],
                    ['name' => 'Control of Movement', 'min_score' => 0, 'max_score' => 20],
                ],
            ],
            [
                'name' => 'Overall Impression (GE)',
                'description' => 'Penilaian general effect meliputi effect dan entertainment, concept dan coordination serta kreatifitas, dan kesatuan variasi.',
                'sub_items' => [
                    ['name' => 'Effect dan Entertainment', 'min_score' => 0, 'max_score' => 40],
                    ['name' => 'Concept dan Coordination & Kreatifitas', 'min_score' => 0, 'max_score' => 40],
                    ['name' => 'Kesatuan dan Variasi', 'min_score' => 0, 'max_score' => 20],
                ],
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
                'school_types' => ['Marching Band', 'Drum Band'],
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

        // Pendaftaran peserta Drumband / Marching Band per divisi
        $prefixes = [
            'Marching Band' => ['Marching Band Bahana Madani', 5],
            'Drum Band' => ['Drum Band Gita Swara', 5],
        ];

        foreach ($prefixes as $division => [$unitName, $count]) {
            for ($i = 1; $i <= $count; $i++) {
                $participant = Participant::create([
                    'school_name' => "{$unitName} Unit {$i}",
                    'school_type' => $division,
                ]);

                $code = $division === 'Marching Band' ? 'MB' : 'DB';
                Registration::create([
                    'competition_id' => $competition->id,
                    'participant_id' => $participant->id,
                    'school_type' => $division,
                    'npp' => "{$code}-".str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $judges
     */
    protected function seedKarnavalCompetition($judges): void
    {
        if (Competition::where('name', 'Lomba Karnaval')->exists()) {
            return;
        }

        $competition = Competition::create([
            'name' => 'Lomba Karnaval',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(6),
        ]);

        $criteriaDefinitions = [
            [
                'name' => 'Kreativitas',
                'description' => "Indikator Penilaian:\na. Kemampuan mengekspresikan ide/gagasan\nb. Kemampuan mengolah gaya dan penampilan\nc. Kemampuan penggunaan property pendukung\nd. Kemampuan Menyesuaikan Tema yang dipilih",
                'min_score' => 0,
                'max_score' => 100,
            ],
            [
                'name' => 'Disiplin dan Semangat',
                'description' => "Indikator Penilaian:\na. Kerjasama Tim\nb. Kerapian Barisan\nc. Semangat Kebersamaan\nd. Patuh pada peraturan Lomba dan Kepatuhan pada adat istiadat dan budaya Indonesia",
                'min_score' => 0,
                'max_score' => 100,
            ],
            [
                'name' => 'Kerapian dan Keselarasan',
                'description' => "Indikator Penilaian:\na. Keindahan/kerapian busana peserta\nb. Postur peserta yang sedap dipandang\nc. Pengaturan Busana sesuai peran\nd. Keindahan properti dan peralatan yang digunakan",
                'min_score' => 0,
                'max_score' => 100,
            ],
        ];

        foreach ($criteriaDefinitions as $def) {
            $criterion = Criterion::create([
                'competition_id' => $competition->id,
                'name' => $def['name'],
                'description' => $def['description'],
                'school_types' => ['SD', 'SMP', 'SMA', 'Umum'],
                'min_score' => $def['min_score'],
                'max_score' => $def['max_score'],
            ]);

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

        // Pendaftaran peserta Karnaval per jenjang
        $prefixes = [
            'SD' => ['Kontingen Karnaval Cilik', 5],
            'SMP' => ['Kontingen Karnaval Muda', 5],
            'SMA' => ['Kontingen Karnaval Madya', 5],
            'Umum' => ['Kontingen Karnaval Budaya', 5],
        ];

        foreach ($prefixes as $schoolType => [$unitName, $count]) {
            for ($i = 1; $i <= $count; $i++) {
                $participant = Participant::create([
                    'school_name' => "{$unitName} {$schoolType} {$i}",
                    'school_type' => $schoolType,
                ]);

                Registration::create([
                    'competition_id' => $competition->id,
                    'participant_id' => $participant->id,
                    'school_type' => $schoolType,
                    'npp' => "KV-{$schoolType}-".str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
