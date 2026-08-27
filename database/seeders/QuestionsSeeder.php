<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;

class QuestionsSeeder extends Seeder
{
    // Quiz Type IDs
    const QT_MC       = 1;  // Multiple Choice
    const QT_TF       = 2;  // True / False
    const QT_MATCH    = 3;  // Matching
    const QT_SORT     = 4;  // Drag & Sort
    const QT_SEQ      = 5;  // Drag Sequence
    const QT_LISTEN   = 6;  // Listen & Choose
    const QT_SPEAK    = 7;  // Speak & Repeat
    const QT_FILL     = 8;  // Spell / Fill Blank
    const QT_COUNT    = 9;  // Count Objects
    const QT_PATTERN  = 10; // Complete Pattern

    // Question Bank IDs (from FirstLearningPathSeeder)
    const BANK_M1  = 22;
    const BANK_M2  = 23;
    const BANK_M3  = 24;
    const BANK_M4  = 25;
    const BANK_M5  = 26;
    const BANK_M6  = 27;
    const BANK_M7  = 28;
    const BANK_M8  = 29;
    const BANK_M9  = 30;
    const BANK_M10 = 31;
    const BANK_M11 = 32;

    public function run(): void
    {
        // Clear existing questions in these banks first
        $bankIds = range(22, 32);
        QuizQuestion::whereIn('question_bank_id', $bankIds)->delete();

        $this->seedM1();
        $this->seedM2();
        $this->seedM3();
        $this->seedM4();
        $this->seedM5();
        $this->seedM6();
        $this->seedM7();
        $this->seedM8();
        $this->seedM9();
        $this->seedM10();
        $this->seedM11();

        $total = QuizQuestion::whereIn('question_bank_id', $bankIds)->count();
        $this->command->info("✅ Seeded {$total} questions across 11 mission banks.");
    }

    // ─── HELPERS ────────────────────────────────────────────────

    private function q(int $bankId, int $typeId, string $prompt, ?string $imageUrl = null, ?string $audioUrl = null, string $difficulty = 'easy', int $sort = 0): QuizQuestion
    {
        return QuizQuestion::create([
            'question_bank_id' => $bankId,
            'quiz_type_id'     => $typeId,
            'prompt'           => $prompt,
            'prompt_image_url' => $imageUrl,
            'prompt_audio_url' => $audioUrl,
            'difficulty'       => $difficulty,
            'sort_order'       => $sort,
            'points'           => 10,
        ]);
    }

    private function opt(QuizQuestion $q, string $text, bool $correct, ?string $imgUrl = null, ?string $matchKey = null, int $sort = 0): void
    {
        QuestionOption::create([
            'question_id'  => $q->id,
            'content_type' => $imgUrl ? 'image' : 'text',
            'text_value'   => $text,
            'image_url'    => $imgUrl,
            'is_correct'   => $correct,
            'match_key'    => $matchKey,
            'sort_order'   => $sort,
        ]);
    }

    private function pairOpts(QuizQuestion $q, array $pairs): void
    {
        // $pairs = [['left'=>'text or null', 'leftImg'=>null, 'right'=>'text or null', 'rightImg'=>null, 'key'=>'1'], ...]
        foreach ($pairs as $i => $p) {
            // Left option
            QuestionOption::create([
                'question_id'  => $q->id,
                'content_type' => !empty($p['leftImg']) ? 'image' : 'text',
                'text_value'   => $p['left'] ?? '',
                'image_url'    => $p['leftImg'] ?? null,
                'is_correct'   => false,
                'match_key'    => $p['key'],
                'sort_order'   => ($i * 2),
            ]);
            // Right option
            QuestionOption::create([
                'question_id'  => $q->id,
                'content_type' => !empty($p['rightImg']) ? 'image' : 'text',
                'text_value'   => $p['right'] ?? '',
                'image_url'    => $p['rightImg'] ?? null,
                'is_correct'   => true,
                'match_key'    => $p['key'],
                'sort_order'   => ($i * 2) + 1,
            ]);
        }
    }

    private function seqOpts(QuizQuestion $q, array $items): void
    {
        // $items = [['text'=>'', 'img'=>null, 'order'=>1], ...]
        foreach ($items as $item) {
            QuestionOption::create([
                'question_id'  => $q->id,
                'content_type' => !empty($item['img']) ? 'image' : 'text',
                'text_value'   => $item['text'] ?? '',
                'image_url'    => $item['img'] ?? null,
                'is_correct'   => false,
                'match_key'    => (string) $item['order'],
                'sort_order'   => $item['order'],
            ]);
        }
    }

    private function bucketOpts(QuizQuestion $q, array $items): void
    {
        // $items = [['text'=>'', 'img'=>null, 'bucket'=>'Category Name'], ...]
        foreach ($items as $i => $item) {
            QuestionOption::create([
                'question_id'  => $q->id,
                'content_type' => !empty($item['img']) ? 'image' : 'text',
                'text_value'   => $item['text'] ?? '',
                'image_url'    => $item['img'] ?? null,
                'is_correct'   => false,
                'match_key'    => $item['bucket'],
                'sort_order'   => $i,
            ]);
        }
    }

    // ─── MISSION 1: Multiple Choice (QT-01) ─────────────────────

    private function seedM1(): void
    {
        $bank = self::BANK_M1;

        // Q1
        $q = $this->q($bank, self::QT_MC, 'How many apples can you see?', '/images/questions/count_apples_03.png');
        $this->opt($q, '1', false, null, null, 0);
        $this->opt($q, '2', false, null, null, 1);
        $this->opt($q, '3', true,  null, null, 2);
        $this->opt($q, '4', false, null, null, 3);

        // Q2
        $q = $this->q($bank, self::QT_MC, 'Count the bananas.', '/images/questions/count_bananas_02.png');
        $this->opt($q, '1', false); $this->opt($q, '2', true); $this->opt($q, '3', false); $this->opt($q, '4', false);

        // Q3
        $q = $this->q($bank, self::QT_MC, 'How many balls are there?', '/images/questions/count_balls_01.png');
        $this->opt($q, '1', true); $this->opt($q, '2', false); $this->opt($q, '3', false); $this->opt($q, '4', false);

        // Q4
        $q = $this->q($bank, self::QT_MC, 'How many stars can you count?', '/images/questions/count_stars_03.png');
        $this->opt($q, '1', false); $this->opt($q, '2', false); $this->opt($q, '3', true); $this->opt($q, '4', false);

        // Q5
        $q = $this->q($bank, self::QT_MC, 'Count the oranges.', '/images/questions/count_oranges_02.png');
        $this->opt($q, '1', false); $this->opt($q, '2', true); $this->opt($q, '3', false); $this->opt($q, '4', false);

        // Q6
        $q = $this->q($bank, self::QT_MC, 'How many flowers are there?', '/images/questions/count_flowers_01.png');
        $this->opt($q, '1', true); $this->opt($q, '2', false); $this->opt($q, '3', false); $this->opt($q, '4', false);

        // Q7
        $q = $this->q($bank, self::QT_MC, 'Count the fish.', '/images/questions/count_fish_03.png');
        $this->opt($q, '1', false); $this->opt($q, '2', false); $this->opt($q, '3', true); $this->opt($q, '4', false);

        // Q8
        $q = $this->q($bank, self::QT_MC, 'How many balloons can you see?', '/images/questions/count_balloons_02.png');
        $this->opt($q, '1', false); $this->opt($q, '2', true); $this->opt($q, '3', false); $this->opt($q, '4', false);

        // Q9
        $q = $this->q($bank, self::QT_MC, 'Count the butterflies.', '/images/questions/count_butterflies_01.png');
        $this->opt($q, '1', true); $this->opt($q, '2', false); $this->opt($q, '3', false); $this->opt($q, '4', false);

        // Q10
        $q = $this->q($bank, self::QT_MC, 'How many toy cars are there?', '/images/questions/count_cars_03.png');
        $this->opt($q, '1', false); $this->opt($q, '2', false); $this->opt($q, '3', true); $this->opt($q, '4', false);

        $this->command->info('M1 (Multiple Choice) — 10 questions seeded.');
    }

    // ─── MISSION 2: Multiple Choice with Images (QT-01) ─────────

    private function seedM2(): void
    {
        $bank = self::BANK_M2;

        $data = [
            ['Count the apples.', '/images/questions/count_apples_04.png', ['3'=>false,'4'=>true,'5'=>false,'6'=>false]],
            ['Count the bananas.', '/images/questions/count_bananas_05.png', ['3'=>false,'4'=>false,'5'=>true,'6'=>false]],
            ['How many balloons are there?', '/images/questions/count_balloons_04.png', ['3'=>false,'4'=>true,'5'=>false,'6'=>false]],
            ['Count the stars.', '/images/questions/count_stars_05.png', ['3'=>false,'4'=>false,'5'=>true,'6'=>false]],
            ['How many oranges can you see?', '/images/questions/count_oranges_04.png', ['3'=>false,'4'=>true,'5'=>false,'6'=>false]],
            ['Count the flowers.', '/images/questions/count_flowers_05.png', ['3'=>false,'4'=>false,'5'=>true,'6'=>false]],
            ['How many fish are swimming?', '/images/questions/count_fish_04.png', ['3'=>false,'4'=>true,'5'=>false,'6'=>false]],
            ['Count the butterflies.', '/images/questions/count_butterflies_05.png', ['3'=>false,'4'=>false,'5'=>true,'6'=>false]],
            ['How many toy cars can you see?', '/images/questions/count_cars_04.png', ['3'=>false,'4'=>true,'5'=>false,'6'=>false]],
            ['Count the pencils.', '/images/questions/count_pencils_05.png', ['3'=>false,'4'=>false,'5'=>true,'6'=>false]],
        ];

        foreach ($data as $row) {
            $q = $this->q($bank, self::QT_MC, $row[0], $row[1]);
            foreach ($row[2] as $label => $correct) {
                $this->opt($q, (string)$label, $correct);
            }
        }

        $this->command->info('M2 (Multiple Choice w/ Images) — 10 questions seeded.');
    }

    // ─── MISSION 3: True / False (QT-02) ────────────────────────

    private function seedM3(): void
    {
        $bank = self::BANK_M3;

        $data = [
            ['There are 6 apples in the picture.',    '/images/questions/tf_apples_06.png',      true],
            ['There are 7 bananas in the picture.',   '/images/questions/tf_bananas_06.png',     false],
            ['There are 7 stars.',                    '/images/questions/tf_stars_07.png',       true],
            ['There are 6 balloons.',                 '/images/questions/tf_balloons_07.png',    false],
            ['There are 6 fish.',                     '/images/questions/tf_fish_06.png',        true],
            ['There are 7 flowers.',                  '/images/questions/tf_flowers_06.png',     false],
            ['There are 7 toy cars.',                 '/images/questions/tf_cars_07.png',        true],
            ['There are 6 pencils.',                  '/images/questions/tf_pencils_07.png',     false],
            ['There are 7 oranges.',                  '/images/questions/tf_oranges_07.png',     true],
            ['There are 6 butterflies.',              '/images/questions/tf_butterflies_07.png', false],
        ];

        foreach ($data as $row) {
            $q = $this->q($bank, self::QT_TF, $row[0], $row[1]);
            $this->opt($q, 'True',  $row[2],  null, null, 0);
            $this->opt($q, 'False', !$row[2], null, null, 1);
        }

        $this->command->info('M3 (True/False) — 10 questions seeded.');
    }

    // ─── MISSION 4: Matching (QT-03) ────────────────────────────

    private function seedM4(): void
    {
        $bank = self::BANK_M4;

        // Q1 — Number text ↔ Apple image
        $q = $this->q($bank, self::QT_MATCH, 'Match each number to the correct group of apples.');
        $this->pairOpts($q, [
            ['left'=>'1', 'leftImg'=>null, 'right'=>'One apple',   'rightImg'=>'/images/questions/match_apple_01.png', 'key'=>'1'],
            ['left'=>'2', 'leftImg'=>null, 'right'=>'Two apples',  'rightImg'=>'/images/questions/match_apple_02.png', 'key'=>'2'],
            ['left'=>'3', 'leftImg'=>null, 'right'=>'Three apples','rightImg'=>'/images/questions/match_apple_03.png', 'key'=>'3'],
        ]);

        // Q2 — Number text ↔ Animal image
        $q = $this->q($bank, self::QT_MATCH, 'Match the number to the animal group.');
        $this->pairOpts($q, [
            ['left'=>'4', 'leftImg'=>null, 'right'=>'Four lions',    'rightImg'=>'/images/questions/match_lions_04.png',   'key'=>'4'],
            ['left'=>'5', 'leftImg'=>null, 'right'=>'Five elephants','rightImg'=>'/images/questions/match_elephants_05.png','key'=>'5'],
            ['left'=>'6', 'leftImg'=>null, 'right'=>'Six monkeys',   'rightImg'=>'/images/questions/match_monkeys_06.png',  'key'=>'6'],
        ]);

        // Q3 — Image ↔ Number text
        $q = $this->q($bank, self::QT_MATCH, 'Match the picture to its number.');
        $this->pairOpts($q, [
            ['left'=>'Seven bananas', 'leftImg'=>'/images/questions/match_bananas_07.png', 'right'=>'7', 'rightImg'=>null, 'key'=>'7'],
            ['left'=>'Eight balls',   'leftImg'=>'/images/questions/match_balls_08.png',   'right'=>'8', 'rightImg'=>null, 'key'=>'8'],
            ['left'=>'Nine stars',    'leftImg'=>'/images/questions/match_stars_09.png',   'right'=>'9', 'rightImg'=>null, 'key'=>'9'],
        ]);

        // Q4 — Number card image ↔ Fish image
        $q = $this->q($bank, self::QT_MATCH, 'Match the number card to the fish group.');
        $this->pairOpts($q, [
            ['left'=>'1','leftImg'=>'/images/questions/numcard_01.png','right'=>'One fish',  'rightImg'=>'/images/questions/match_fish_01.png','key'=>'1'],
            ['left'=>'2','leftImg'=>'/images/questions/numcard_02.png','right'=>'Two fish',  'rightImg'=>'/images/questions/match_fish_02.png','key'=>'2'],
            ['left'=>'3','leftImg'=>'/images/questions/numcard_03.png','right'=>'Three fish','rightImg'=>'/images/questions/match_fish_03.png','key'=>'3'],
        ]);

        // Q5 — Mixed: text + image ↔ image
        $q = $this->q($bank, self::QT_MATCH, 'Match everything correctly!');
        $this->pairOpts($q, [
            ['left'=>'10',           'leftImg'=>null,                                     'right'=>'Ten balloons','rightImg'=>'/images/questions/match_balloons_10.png','key'=>'10'],
            ['left'=>'5 apples',     'leftImg'=>'/images/questions/match_apple_05.png',   'right'=>'5',           'rightImg'=>'/images/questions/numcard_05.png',       'key'=>'5'],
            ['left'=>'7',            'leftImg'=>null,                                     'right'=>'Seven ducks', 'rightImg'=>'/images/questions/match_ducks_07.png',    'key'=>'7'],
        ]);

        $this->command->info('M4 (Matching) — 5 questions seeded.');
    }

    // ─── MISSION 5: Drag & Sort (QT-04) ─────────────────────────

    private function seedM5(): void
    {
        $bank = self::BANK_M5;

        // Q1 — Numbers < 5 vs >= 5
        $q = $this->q($bank, self::QT_SORT, 'Put the pictures into the correct basket.');
        $this->bucketOpts($q, [
            ['text'=>'2', 'img'=>'/images/questions/numcard_02.png', 'bucket'=>'Numbers Less than 5'],
            ['text'=>'4', 'img'=>'/images/questions/numcard_04.png', 'bucket'=>'Numbers Less than 5'],
            ['text'=>'6', 'img'=>'/images/questions/numcard_06.png', 'bucket'=>'Numbers 5 and Above'],
            ['text'=>'8', 'img'=>'/images/questions/numcard_08.png', 'bucket'=>'Numbers 5 and Above'],
        ]);

        // Q2 — Small vs Big animal groups
        $q = $this->q($bank, self::QT_SORT, 'Drag the animals into the correct basket.');
        $this->bucketOpts($q, [
            ['text'=>'2 birds',    'img'=>'/images/questions/sort_birds_02.png',    'bucket'=>'Small Groups'],
            ['text'=>'3 rabbits',  'img'=>'/images/questions/sort_rabbits_03.png',  'bucket'=>'Small Groups'],
            ['text'=>'7 elephants','img'=>'/images/questions/sort_elephants_07.png','bucket'=>'Big Groups'],
            ['text'=>'9 monkeys', 'img'=>'/images/questions/sort_monkeys_09.png',   'bucket'=>'Big Groups'],
        ]);

        // Q3 — Fruits 1–5 vs 6–10
        $q = $this->q($bank, self::QT_SORT, 'Sort the fruit.');
        $this->bucketOpts($q, [
            ['text'=>'1 apple',  'img'=>'/images/questions/sort_apple_01.png',  'bucket'=>'One to Five'],
            ['text'=>'3 oranges','img'=>'/images/questions/sort_orange_03.png', 'bucket'=>'One to Five'],
            ['text'=>'6 bananas','img'=>'/images/questions/sort_banana_06.png', 'bucket'=>'Six to Ten'],
            ['text'=>'9 mangoes','img'=>'/images/questions/sort_mango_09.png',  'bucket'=>'Six to Ten'],
        ]);

        // Q4 — Even vs Odd
        $q = $this->q($bank, self::QT_SORT, 'Put the numbers where they belong.');
        $this->bucketOpts($q, [
            ['text'=>'2', 'img'=>'/images/questions/numcard_02.png', 'bucket'=>'Even Numbers'],
            ['text'=>'6', 'img'=>'/images/questions/numcard_06.png', 'bucket'=>'Even Numbers'],
            ['text'=>'3', 'img'=>'/images/questions/numcard_03.png', 'bucket'=>'Odd Numbers'],
            ['text'=>'9', 'img'=>'/images/questions/numcard_09.png', 'bucket'=>'Odd Numbers'],
        ]);

        // Q5 — Numbers vs Objects
        $q = $this->q($bank, self::QT_SORT, 'Sort everything correctly.');
        $this->bucketOpts($q, [
            ['text'=>'5',          'img'=>'/images/questions/numcard_05.png',       'bucket'=>'Numbers'],
            ['text'=>'8',          'img'=>'/images/questions/numcard_08.png',       'bucket'=>'Numbers'],
            ['text'=>'5 pencils',  'img'=>'/images/questions/sort_pencils_05.png',  'bucket'=>'Objects'],
            ['text'=>'8 books',    'img'=>'/images/questions/sort_books_08.png',    'bucket'=>'Objects'],
        ]);

        $this->command->info('M5 (Drag & Sort) — 5 questions seeded.');
    }

    // ─── MISSION 6: Drag Sequence (QT-05) ───────────────────────

    private function seedM6(): void
    {
        $bank = self::BANK_M6;

        // Q1
        $q = $this->q($bank, self::QT_SEQ, 'Put the numbers in the correct counting order.');
        $this->seqOpts($q, [
            ['text'=>'2','img'=>'/images/questions/numcard_02.png','order'=>2],
            ['text'=>'1','img'=>'/images/questions/numcard_01.png','order'=>1],
            ['text'=>'3','img'=>'/images/questions/numcard_03.png','order'=>3],
        ]);

        // Q2
        $q = $this->q($bank, self::QT_SEQ, 'Drag the numbers into the correct order.');
        $this->seqOpts($q, [
            ['text'=>'6','img'=>'/images/questions/numcard_06.png','order'=>3],
            ['text'=>'4','img'=>'/images/questions/numcard_04.png','order'=>1],
            ['text'=>'5','img'=>'/images/questions/numcard_05.png','order'=>2],
        ]);

        // Q3
        $q = $this->q($bank, self::QT_SEQ, 'Put the groups in order from the smallest to the biggest.');
        $this->seqOpts($q, [
            ['text'=>'Three apples','img'=>'/images/questions/seq_apple_03.png','order'=>3],
            ['text'=>'One apple',   'img'=>'/images/questions/seq_apple_01.png','order'=>1],
            ['text'=>'Two apples',  'img'=>'/images/questions/seq_apple_02.png','order'=>2],
        ]);

        // Q4 — mixed image + text
        $q = $this->q($bank, self::QT_SEQ, 'Put the numbers in the correct order.');
        $this->seqOpts($q, [
            ['text'=>'8', 'img'=>'/images/questions/numcard_08.png','order'=>2],
            ['text'=>'10','img'=>null,                              'order'=>4],
            ['text'=>'9', 'img'=>'/images/questions/numcard_09.png','order'=>3],
            ['text'=>'7', 'img'=>null,                              'order'=>1],
        ]);

        // Q5
        $q = $this->q($bank, self::QT_SEQ, 'Put the animal groups in order from the fewest to the most.');
        $this->seqOpts($q, [
            ['text'=>'Four ducks', 'img'=>'/images/questions/seq_duck_04.png','order'=>4],
            ['text'=>'One duck',   'img'=>'/images/questions/seq_duck_01.png','order'=>1],
            ['text'=>'Three ducks','img'=>'/images/questions/seq_duck_03.png','order'=>3],
            ['text'=>'Two ducks',  'img'=>'/images/questions/seq_duck_02.png','order'=>2],
        ]);

        $this->command->info('M6 (Drag Sequence) — 5 questions seeded.');
    }

    // ─── MISSION 7: Listen & Choose (QT-06) ─────────────────────

    private function seedM7(): void
    {
        $bank = self::BANK_M7;

        // Q1
        $q = $this->q($bank, self::QT_LISTEN, 'Tap the picture with three apples.');
        $this->opt($q, 'One apple',   false, '/images/questions/listen_apple_01.png');
        $this->opt($q, 'Two apples',  false, '/images/questions/listen_apple_02.png');
        $this->opt($q, 'Three apples',true,  '/images/questions/listen_apple_03.png');
        $this->opt($q, 'Four apples', false, '/images/questions/listen_apple_04.png');

        // Q2
        $q = $this->q($bank, self::QT_LISTEN, 'Tap the number five.');
        $this->opt($q, '3',  false, '/images/questions/numcard_03.png');
        $this->opt($q, '5',  true,  '/images/questions/numcard_05.png');
        $this->opt($q, '7',  false, '/images/questions/numcard_07.png');
        $this->opt($q, '9',  false, '/images/questions/numcard_09.png');

        // Q3
        $q = $this->q($bank, self::QT_LISTEN, 'Which picture shows six balloons?');
        $this->opt($q, 'Three balloons', false, '/images/questions/listen_balloons_03.png');
        $this->opt($q, 'Five balloons',  false, '/images/questions/listen_balloons_05.png');
        $this->opt($q, 'Six balloons',   true,  '/images/questions/listen_balloons_06.png');
        $this->opt($q, 'Eight balloons', false, '/images/questions/listen_balloons_08.png');

        // Q4
        $q = $this->q($bank, self::QT_LISTEN, 'Tap the picture with two cats.');
        $this->opt($q, 'One cat',   false, '/images/questions/listen_cat_01.png');
        $this->opt($q, 'Two cats',  true,  '/images/questions/listen_cat_02.png');
        $this->opt($q, 'Three cats',false, '/images/questions/listen_cat_03.png');
        $this->opt($q, 'Four cats', false, '/images/questions/listen_cat_04.png');

        // Q5 — mixed image + text options
        $q = $this->q($bank, self::QT_LISTEN, 'Tap the number ten.');
        $this->opt($q, '8',  false, '/images/questions/numcard_08.png');
        $this->opt($q, '9',  false); // text-only
        $this->opt($q, '10', true,  '/images/questions/numcard_10.png');
        $this->opt($q, '6',  false); // text-only

        $this->command->info('M7 (Listen & Choose) — 5 questions seeded.');
    }

    // ─── MISSION 8: Speak & Repeat (QT-07) ──────────────────────

    private function seedM8(): void
    {
        $bank = self::BANK_M8;

        $data = [
            ['Say the number one.',         '/images/questions/speak_num_01.png'],
            ['Say the number three.',        '/images/questions/speak_num_03.png'],
            ['Say the number five.',         '/images/questions/speak_num_05.png'],
            ['Say the number eight.',        '/images/questions/speak_num_08.png'],
            ['Count with me: One, two, three!', '/images/questions/speak_123.png'],
        ];

        foreach ($data as $row) {
            $this->q($bank, self::QT_SPEAK, $row[0], $row[1]);
        }

        $this->command->info('M8 (Speak & Repeat) — 5 questions seeded.');
    }

    // ─── MISSION 9: Fill the Blank (QT-08) ──────────────────────

    private function seedM9(): void
    {
        $bank = self::BANK_M9;

        // Q1: 1, 2, __ → 3
        $q = $this->q($bank, self::QT_FILL, "Which number comes next?\n1, 2, __");
        $this->opt($q, '3', true,  '/images/questions/numcard_03.png');
        $this->opt($q, '5', false, '/images/questions/numcard_05.png');
        $this->opt($q, '4', false);
        $this->opt($q, '6', false, '/images/questions/numcard_06.png');

        // Q2: __, 5, 6 → 4
        $q = $this->q($bank, self::QT_FILL, "Fill in the missing number.\n__, 5, 6");
        $this->opt($q, '4', true,  '/images/questions/numcard_04.png');
        $this->opt($q, '2', false, '/images/questions/numcard_02.png');
        $this->opt($q, '7', false, '/images/questions/numcard_07.png');
        $this->opt($q, '8', false, '/images/questions/numcard_08.png');

        // Q3: 7, __, 9 → 8
        $q = $this->q($bank, self::QT_FILL, "Which number is missing?\n7, __, 9");
        $this->opt($q, '8',  true);
        $this->opt($q, '6',  false, '/images/questions/numcard_06.png');
        $this->opt($q, '10', false, '/images/questions/numcard_10.png');
        $this->opt($q, '5',  false);

        // Q4: 8, 9, __ → 10
        $q = $this->q($bank, self::QT_FILL, "Complete the counting.\n8, 9, __");
        $this->opt($q, '10', true,  '/images/questions/numcard_10.png');
        $this->opt($q, '7',  false, '/images/questions/numcard_07.png');
        $this->opt($q, '5',  false, '/images/questions/numcard_05.png');
        $this->opt($q, '4',  false, '/images/questions/numcard_04.png');

        // Q5: 3, 4, __ → 5
        $q = $this->q($bank, self::QT_FILL, "Count forward.\n3, 4, __");
        $this->opt($q, '5', true,  '/images/questions/numcard_05.png');
        $this->opt($q, '2', false);
        $this->opt($q, '6', false, '/images/questions/numcard_06.png');
        $this->opt($q, '7', false);

        $this->command->info('M9 (Fill the Blank) — 5 questions seeded.');
    }

    // ─── MISSION 10: Count Objects (QT-09) ──────────────────────

    private function seedM10(): void
    {
        $bank = self::BANK_M10;

        $data = [
            ['Count the apples. How many apples are there?', '/images/questions/co_apples_03.png',
                [['2',false,'/images/questions/numcard_02.png'],['3',true,'/images/questions/numcard_03.png'],['4',false,null],['5',false,'/images/questions/numcard_05.png']]],
            ['Count the balloons.', '/images/questions/co_balloons_05.png',
                [['4',false,'/images/questions/numcard_04.png'],['5',true,'/images/questions/numcard_05.png'],['6',false,'/images/questions/numcard_06.png'],['7',false,'/images/questions/numcard_07.png']]],
            ['How many fish can you count?', '/images/questions/co_fish_07.png',
                [['6',false,null],['7',true,'/images/questions/numcard_07.png'],['8',false,'/images/questions/numcard_08.png'],['9',false,null]]],
            ['Count the stars.', '/images/questions/co_stars_09.png',
                [['8',false,'/images/questions/numcard_08.png'],['9',true,'/images/questions/numcard_09.png'],['10',false,'/images/questions/numcard_10.png'],['7',false,'/images/questions/numcard_07.png']]],
            ['Count the pencils.', '/images/questions/co_pencils_06.png',
                [['5',false,'/images/questions/numcard_05.png'],['6',true,'/images/questions/numcard_06.png'],['7',false,null],['8',false,'/images/questions/numcard_08.png']]],
        ];

        foreach ($data as $row) {
            $q = $this->q($bank, self::QT_COUNT, $row[0], $row[1]);
            foreach ($row[2] as $i => $opt) {
                $this->opt($q, $opt[0], $opt[1], $opt[2], null, $i);
            }
        }

        $this->command->info('M10 (Count Objects) — 5 questions seeded.');
    }

    // ─── MISSION 11: Complete the Pattern (QT-10) ───────────────

    private function seedM11(): void
    {
        $bank = self::BANK_M11;

        // Q1 — Apple/Banana pattern
        $q = $this->q($bank, self::QT_PATTERN, 'Which picture comes next?', '/images/questions/pattern_apple_banana.png');
        $this->opt($q, 'Apple',      true,  '/images/questions/pat_apple.png');
        $this->opt($q, 'Strawberry', false, '/images/questions/pat_strawberry.png');
        $this->opt($q, 'Grapes',     false, '/images/questions/pat_grapes.png');
        $this->opt($q, 'Pineapple',  false, '/images/questions/pat_pineapple.png');

        // Q2 — Star/Heart pattern
        $q = $this->q($bank, self::QT_PATTERN, 'Complete the pattern.', '/images/questions/pattern_star_heart.png');
        $this->opt($q, 'Heart', true,  '/images/questions/pat_heart.png');
        $this->opt($q, 'Star',  false, '/images/questions/pat_star.png');
        $this->opt($q, 'Moon',  false, '/images/questions/pat_moon.png');
        $this->opt($q, 'Sun',   false, '/images/questions/pat_sun.png');

        // Q3 — 1/2 number pattern (mixed)
        $q = $this->q($bank, self::QT_PATTERN, 'Which number comes next?', '/images/questions/pattern_1_2.png');
        $this->opt($q, '1', true,  '/images/questions/numcard_01.png');
        $this->opt($q, '3', false);
        $this->opt($q, '2', false, '/images/questions/numcard_02.png');
        $this->opt($q, '4', false);

        // Q4 — Dog/Cat pattern
        $q = $this->q($bank, self::QT_PATTERN, 'Look carefully. Which picture comes next?', '/images/questions/pattern_dog_cat.png');
        $this->opt($q, 'Dog',    true,  '/images/questions/pat_dog.png');
        $this->opt($q, 'Rabbit', false, '/images/questions/pat_rabbit.png');
        $this->opt($q, 'Bear',   false, '/images/questions/pat_bear.png');
        $this->opt($q, 'Lion',   false, '/images/questions/pat_lion.png');

        // Q5 — Red/Blue colour pattern
        $q = $this->q($bank, self::QT_PATTERN, 'Complete the colour pattern.', '/images/questions/pattern_red_blue.png');
        $this->opt($q, 'Red',    true,  '/images/questions/pat_red_circle.png');
        $this->opt($q, 'Green',  false, '/images/questions/pat_green_circle.png');
        $this->opt($q, 'Yellow', false, '/images/questions/pat_yellow_circle.png');
        $this->opt($q, 'Purple', false, '/images/questions/pat_purple_circle.png');

        $this->command->info('M11 (Complete the Pattern) — 5 questions seeded.');
    }
}
