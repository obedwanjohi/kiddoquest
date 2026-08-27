<?php

namespace Database\Seeders;

use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\WorldLesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1; // Super Admin

        // ── Clear existing content (force delete to bypass soft deletes + unique slugs) ──
        // IMPORTANT: Clear world_lessons FIRST so we can re-link fresh lessons below.
        WorldLesson::query()->forceDelete();
        QuestionOption::query()->forceDelete();
        QuizQuestion::query()->forceDelete();
        Quiz::query()->forceDelete();
        Lesson::query()->forceDelete();
        Topic::query()->forceDelete();
        Subject::query()->forceDelete();

        // ── SUBJECT 1: Alphabet 🅰️ ─────────────────────────────────
        $alphabet = $this->createSubject('Alphabet', '🔤', '#EF4444', 'Learn the 26 letters from A to Z through fun activities!');
        $lettersTopic = $this->createTopic($alphabet->id, 'Letters A–Z', '🔡', $adminId);

        $lessonA = $this->createLesson($lettersTopic->id, 'Meet the Letter A', $adminId, 'A is for Apple, Ant, and Alligator! Learn the first letter of the alphabet.', 4);
        $lessonB = $this->createLesson($lettersTopic->id, 'Meet the Letter B', $adminId, 'B is for Ball, Bear, and Butterfly! Discover the second letter.', 4);
        $lessonC = $this->createLesson($lettersTopic->id, 'Meet the Letter C', $adminId, 'C is for Cat, Cake, and Car! Explore the curvy letter C.', 4);

        // Quiz for Lesson A
        $quizA = $this->createQuiz($lessonA->id, 'Letter A — Quick Check', $adminId);
        $mcTypeId = QuizType::where('code', 'QT-01')->value('id');
        $tfTypeId = QuizType::where('code', 'QT-02')->value('id');
        $spellTypeId = QuizType::where('code', 'QT-08')->value('id');

        $q1 = QuizQuestion::create([
            'quiz_id' => $quizA->id, 'quiz_type_id' => $mcTypeId,
            'prompt' => 'Which word starts with the letter A?',
            'points' => 2, 'sort_order' => 0,
            'hint' => 'Think of a red fruit.', 'explanation' => 'Apple starts with A!',
            'prompt_image_url' => '/media/apple.jpg',
        ]);
        $this->addOption($q1->id, 'text', 'Apple', true, 0);
        $this->addOption($q1->id, 'text', 'Ball', false, 1);
        $this->addOption($q1->id, 'text', 'Cat', false, 2);
        $this->addOption($q1->id, 'text', 'Dog', false, 3);

        $q2 = QuizQuestion::create([
            'quiz_id' => $quizA->id, 'quiz_type_id' => $tfTypeId,
            'prompt' => 'The word "Ant" starts with the letter A.',
            'points' => 1, 'sort_order' => 1,
            'explanation' => 'Yes! A-n-t starts with A.',
        ]);
        $this->addOption($q2->id, 'text', 'True', true, 0);
        $this->addOption($q2->id, 'text', 'False', false, 1);

        $q3 = QuizQuestion::create([
            'quiz_id' => $quizA->id, 'quiz_type_id' => $spellTypeId,
            'prompt' => 'Fill the blank:  _ P P L E',
            'points' => 3, 'sort_order' => 2,
            'hint' => "It's the first letter of the alphabet.",
            'explanation' => 'The missing letter is A!',
        ]);
        $this->addOption($q3->id, 'text', 'A', true, 0);
        $this->addOption($q3->id, 'text', 'B', false, 1);
        $this->addOption($q3->id, 'text', 'E', false, 2);

        // Matching question (QT-03) — added to Letter A quiz
        $matchingTypeIdA = QuizType::where('code', 'QT-03')->value('id');
        $qMatch = QuizQuestion::create([
            'quiz_id' => $quizA->id, 'quiz_type_id' => $matchingTypeIdA,
            'prompt' => 'Match each picture to its first letter!',
            'points' => 4, 'sort_order' => 3,
            'hint' => 'Say the word out loud and listen for the first sound.',
            'explanation' => 'Every word starts with a letter! Apple starts with A.',
        ]);
        $this->addOption($qMatch->id, 'text', '🍎 Apple', false, 0, 'A');
        $this->addOption($qMatch->id, 'text', '🐝 Bee', false, 1, 'B');
        $this->addOption($qMatch->id, 'text', '🐱 Cat', false, 2, 'C');
        $this->addOption($qMatch->id, 'text', '☀️ Sun', false, 3, 'S');

        // ── SUBJECT 2: Numbers 🔢 ───────────────────────────────────
        $numbers = $this->createSubject('Numbers', '🔢', '#3B82F6', 'Counting and number recognition for little mathematicians!');
        $countingTopic = $this->createTopic($numbers->id, 'Counting 1–10', '🧮', $adminId);

        $lesson1 = $this->createLesson($countingTopic->id, 'Counting 1 to 5', $adminId, "Let's count from 1 to 5 with fingers and toys!", 5);
        $lesson2 = $this->createLesson($countingTopic->id, 'Counting 6 to 10', $adminId, 'Continue the counting adventure from 6 to 10!', 5);

        $quiz1 = $this->createQuiz($lesson1->id, 'Counting 1–5 Quiz', $adminId);
        $countTypeId = QuizType::where('code', 'QT-09')->value('id');
        $seqTypeId = QuizType::where('code', 'QT-05')->value('id');
        $patternTypeId = QuizType::where('code', 'QT-10')->value('id');

        $q4 = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $countTypeId,
            'prompt' => 'How many apples do you see?',
            'points' => 2, 'sort_order' => 0,
            'hint' => 'Point at each apple and count out loud: 1... 2... 3...',
            'explanation' => 'There are 3 apples! 🍎🍎🍎',
            'metadata' => ['objects' => ['🍎','🍎','🍎'], 'label' => 'apples'],
        ]);
        $this->addOption($q4->id, 'text', '2', false, 0);
        $this->addOption($q4->id, 'text', '3', true, 1);
        $this->addOption($q4->id, 'text', '4', false, 2);
        $this->addOption($q4->id, 'text', '5', false, 3);

        // QT-09 #2 — Count the ducks (2)
        $q4b = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $countTypeId,
            'prompt' => 'Count the little ducks! How many?',
            'points' => 2, 'sort_order' => 0,
            'hint' => 'Touch each duck as you count.',
            'explanation' => 'There are 2 ducks! 🦆🦆',
            'metadata' => ['objects' => ['🦆','🦆'], 'label' => 'ducks'],
        ]);
        $this->addOption($q4b->id, 'text', '1', false, 0);
        $this->addOption($q4b->id, 'text', '2', true, 1);
        $this->addOption($q4b->id, 'text', '3', false, 2);
        $this->addOption($q4b->id, 'text', '5', false, 3);

        // QT-09 #3 — Count the stars (5)
        $q4c = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $countTypeId,
            'prompt' => 'How many stars are shining?',
            'points' => 3, 'sort_order' => 0,
            'hint' => 'Count each star one by one.',
            'explanation' => 'There are 5 stars! ⭐⭐⭐⭐⭐',
            'metadata' => ['objects' => ['⭐','⭐','⭐','⭐','⭐'], 'label' => 'stars'],
        ]);
        $this->addOption($q4c->id, 'text', '3', false, 0);
        $this->addOption($q4c->id, 'text', '4', false, 1);
        $this->addOption($q4c->id, 'text', '5', true, 2);
        $this->addOption($q4c->id, 'text', '6', false, 3);

        $q5 = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $seqTypeId,
            'prompt' => 'Put these numbers in order from smallest to biggest: 3, 1, 2',
            'points' => 3, 'sort_order' => 1,
            'hint' => 'Start with the smallest number.',
        ]);
        $this->addOption($q5->id, 'text', '1', false, 0, 'pos1');
        $this->addOption($q5->id, 'text', '2', false, 1, 'pos2');
        $this->addOption($q5->id, 'text', '3', false, 2, 'pos3');

        $q6 = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $patternTypeId,
            'prompt' => 'What comes next in the pattern?',
            'points' => 2, 'sort_order' => 2,
            'hint' => 'Count up by 1 each time: 1... 2... 3... 4... what?',
            'explanation' => 'The next number is 5 — we count up by 1!',
            'metadata' => ['sequence' => ['1','2','3','4'], 'missing_index' => 4],
        ]);
        $this->addOption($q6->id, 'text', '5', true, 0);
        $this->addOption($q6->id, 'text', '6', false, 1);
        $this->addOption($q6->id, 'text', '10', false, 2);

        // QT-10 #2 — Counting by 2s
        $q6b = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $patternTypeId,
            'prompt' => 'Complete the pattern! What is missing?',
            'points' => 3, 'sort_order' => 2,
            'hint' => 'We skip one each time: 2... 4... 6... what comes next?',
            'explanation' => 'We are counting by 2s! After 6 comes 8!',
            'metadata' => ['sequence' => ['2','4','6'], 'missing_index' => 3],
        ]);
        $this->addOption($q6b->id, 'text', '7', false, 0);
        $this->addOption($q6b->id, 'text', '8', true, 1);
        $this->addOption($q6b->id, 'text', '9', false, 2);
        $this->addOption($q6b->id, 'text', '10', false, 3);

        // QT-10 #3 — Color pattern (🔴🔵🔴🔵🔴 ?)
        $q6c = QuizQuestion::create([
            'quiz_id' => $quiz1->id, 'quiz_type_id' => $patternTypeId,
            'prompt' => 'Look at the colors! What comes next?',
            'points' => 2, 'sort_order' => 2,
            'hint' => 'Red, Blue, Red, Blue, Red... what color is next?',
            'explanation' => 'The pattern goes Red, Blue, Red, Blue! So next is Blue! 🔵',
            'metadata' => ['sequence' => ['🔴','🔵','🔴','🔵','🔴'], 'missing_index' => 5],
        ]);
        $this->addOption($q6c->id, 'text', '🔵', true, 0);
        $this->addOption($q6c->id, 'text', '🔴', false, 1);
        $this->addOption($q6c->id, 'text', '🟢', false, 2);
        $this->addOption($q6c->id, 'text', '🟡', false, 3);

        // ── SUBJECT 3: Colors 🎨 ────────────────────────────────────
        $colors = $this->createSubject('Colors', '🌈', '#8B5CF6', 'Discover the rainbow and learn to name all the colors!');
        $basicColorsTopic = $this->createTopic($colors->id, 'Rainbow Colors', '🌈', $adminId);

        $lessonRed = $this->createLesson($basicColorsTopic->id, 'The Color Red', $adminId, 'Red like a strawberry, a fire truck, and a heart! ❤️', 3);
        $lessonBlue = $this->createLesson($basicColorsTopic->id, 'The Color Blue', $adminId, 'Blue like the sky, the ocean, and a blueberry! 💙', 3);

        $quizColors = $this->createQuiz($lessonRed->id, 'Color Red Quiz', $adminId);
        $listenTypeId = QuizType::where('code', 'QT-06')->value('id');
        $matchingTypeId = QuizType::where('code', 'QT-03')->value('id');

        $q7 = QuizQuestion::create([
            'quiz_id' => $quizColors->id, 'quiz_type_id' => $mcTypeId,
            'prompt' => 'Which of these is RED?',
            'points' => 2, 'sort_order' => 0,
            'explanation' => 'A strawberry is red!',
        ]);
        $this->addOption($q7->id, 'text', '🍎 Strawberry', true, 0);
        $this->addOption($q7->id, 'text', '🍌 Banana', false, 1);
        $this->addOption($q7->id, 'text', '🥑 Avocado', false, 2);
        $this->addOption($q7->id, 'text', '🍇 Grape', false, 3);

        $q8 = QuizQuestion::create([
            'quiz_id' => $quizColors->id, 'quiz_type_id' => $matchingTypeId,
            'prompt' => 'Match each fruit to its color!',
            'points' => 3, 'sort_order' => 1,
        ]);
        $this->addOption($q8->id, 'text', 'Apple', false, 0, 'red');
        $this->addOption($q8->id, 'text', 'Banana', false, 1, 'yellow');
        $this->addOption($q8->id, 'text', 'Sky', false, 2, 'blue');
        $this->addOption($q8->id, 'text', 'Grass', false, 3, 'green');

        // ── SUBJECT 4: Animals 🐾 ───────────────────────────────────
        $animals = $this->createSubject('Animals', '🐾', '#10B981', 'Meet farm animals, pets, and wild creatures from around the world!');
        $farmTopic = $this->createTopic($animals->id, 'Farm Animals', '🚜', $adminId);

        $lessonFarm = $this->createLesson($farmTopic->id, 'Animals on the Farm', $adminId, "Cows, pigs, chickens, and sheep — let's meet the farm friends! 🐄🐷🐔🐑", 6);
        $quizFarm = $this->createQuiz($lessonFarm->id, 'Farm Friends Quiz', $adminId);
        $speakTypeId = QuizType::where('code', 'QT-07')->value('id');

        // QT-06 #1 — Cow says "Moo" (using Web Speech API — no audio file needed!)
        $q9 = QuizQuestion::create([
            'quiz_id' => $quizFarm->id, 'quiz_type_id' => $listenTypeId,
            'prompt' => 'Listen! Which animal says "Moo"?',
            'points' => 2, 'sort_order' => 0,
            'hint' => 'A big farm animal with white and black spots!',
            'explanation' => 'The cow says Moo! 🐄',
            'metadata' => ['audio_text' => 'Moo! Moo!'],
        ]);
        $this->addOption($q9->id, 'text', '🐄 Cow', true, 0);
        $this->addOption($q9->id, 'text', '🐷 Pig', false, 1);
        $this->addOption($q9->id, 'text', '🐔 Chicken', false, 2);
        $this->addOption($q9->id, 'text', '🐴 Horse', false, 3);

        // QT-06 #2 — Duck says "Quack"
        $q9b = QuizQuestion::create([
            'quiz_id' => $quizFarm->id, 'quiz_type_id' => $listenTypeId,
            'prompt' => 'Listen carefully! Which animal says "Quack"?',
            'points' => 2, 'sort_order' => 0,
            'hint' => 'This animal loves water and has a flat beak!',
            'explanation' => 'The duck says Quack! 🦆',
            'metadata' => ['audio_text' => 'Quack! Quack!'],
        ]);
        $this->addOption($q9b->id, 'text', '🐕 Dog', false, 0);
        $this->addOption($q9b->id, 'text', '🐈 Cat', false, 1);
        $this->addOption($q9b->id, 'text', '🦆 Duck', true, 2);
        $this->addOption($q9b->id, 'text', '🐑 Sheep', false, 3);

        // QT-06 #3 — Sheep says "Baa"
        $q9c = QuizQuestion::create([
            'quiz_id' => $quizFarm->id, 'quiz_type_id' => $listenTypeId,
            'prompt' => 'What sound is this? Which animal is it?',
            'points' => 3, 'sort_order' => 0,
            'hint' => 'This animal has fluffy white wool!',
            'explanation' => 'The sheep says Baa! 🐑',
            'metadata' => ['audio_text' => 'Baa! Baa!'],
        ]);
        $this->addOption($q9c->id, 'text', '🐮 Cow', false, 0);
        $this->addOption($q9c->id, 'text', '🐷 Pig', false, 1);
        $this->addOption($q9c->id, 'text', '🐴 Horse', false, 2);
        $this->addOption($q9c->id, 'text', '🐑 Sheep', true, 3);

        $q10 = QuizQuestion::create([
            'quiz_id' => $quizFarm->id, 'quiz_type_id' => $speakTypeId,
            'prompt' => 'Say the word:  "SHEEP"',
            'points' => 2, 'sort_order' => 1,
            'prompt_audio_url' => '/media/say-sheep.mp3',
            'explanation' => 'Great job saying sheep! 🐑',
        ]);

        // ── SUBJECT 5: Shapes 📐 ────────────────────────────────────
        $shapes = $this->createSubject('Shapes', '🔷', '#F59E0B', 'Circles, squares, triangles, and more — shapes are everywhere!');
        $basicShapesTopic = $this->createTopic($shapes->id, 'Basic Shapes', '⭐', $adminId);

        $lessonShapes = $this->createLesson($basicShapesTopic->id, 'Circle, Square, Triangle', $adminId, 'A circle is round, a square has 4 sides, and a triangle has 3 corners!', 5);
        $quizShapes = $this->createQuiz($lessonShapes->id, 'Shapes Quiz', $adminId);

        $q11 = QuizQuestion::create([
            'quiz_id' => $quizShapes->id, 'quiz_type_id' => $mcTypeId,
            'prompt' => 'Which shape has 3 corners?',
            'points' => 2, 'sort_order' => 0,
            'explanation' => 'A triangle has 3 corners!',
        ]);
        $this->addOption($q11->id, 'text', '🔺 Triangle', true, 0);
        $this->addOption($q11->id, 'text', '⬛ Square', false, 1);
        $this->addOption($q11->id, 'text', '⭕ Circle', false, 2);
        $this->addOption($q11->id, 'text', '⭐ Star', false, 3);

        $q12 = QuizQuestion::create([
            'quiz_id' => $quizShapes->id, 'quiz_type_id' => $tfTypeId,
            'prompt' => 'A circle has 4 sides.',
            'points' => 1, 'sort_order' => 1,
            'explanation' => 'No! A circle is round — it has 0 sides.',
        ]);
        $this->addOption($q12->id, 'text', 'True', false, 0);
        $this->addOption($q12->id, 'text', 'False', true, 1);

        // QT-04 #1 — Sort: Farm vs Wild Animals
        $sortTypeId = QuizType::where('code', 'QT-04')->value('id');
        $qSort1 = QuizQuestion::create([
            'quiz_id' => $quizFarm->id, 'quiz_type_id' => $sortTypeId,
            'prompt' => 'Sort the animals! Farm or Wild?',
            'points' => 4, 'sort_order' => 2,
            'hint' => 'Farm animals live with people. Wild animals live in the jungle!',
            'explanation' => 'Pigs and cows live on a farm. Lions and elephants live in the wild!',
            'metadata' => ['categories' => ['🚜 Farm', '🌿 Wild']],
        ]);
        $this->addOption($qSort1->id, 'text', '🐷 Pig', false, 0, '🚜 Farm');
        $this->addOption($qSort1->id, 'text', '🦁 Lion', false, 1, '🌿 Wild');
        $this->addOption($qSort1->id, 'text', '🐄 Cow', false, 2, '🚜 Farm');
        $this->addOption($qSort1->id, 'text', '🐘 Elephant', false, 3, '🌿 Wild');

        // QT-04 #2 — Sort: Big vs Small
        $qSort2 = QuizQuestion::create([
            'quiz_id' => $quizShapes->id, 'quiz_type_id' => $sortTypeId,
            'prompt' => 'Sort by size! Big or Small?',
            'points' => 4, 'sort_order' => 2,
            'hint' => 'Big things are huge! Small things are tiny!',
            'explanation' => 'Elephants and whales are big. Mice and ants are small!',
            'metadata' => ['categories' => ['🐘 Big', '🐭 Small']],
        ]);
        $this->addOption($qSort2->id, 'text', '🐘 Elephant', false, 0, '🐘 Big');
        $this->addOption($qSort2->id, 'text', '🐭 Mouse', false, 1, '🐭 Small');
        $this->addOption($qSort2->id, 'text', '🐳 Whale', false, 2, '🐘 Big');
        $this->addOption($qSort2->id, 'text', '🐜 Ant', false, 3, '🐭 Small');

        // QT-04 #3 — Sort: Red vs Blue
        $qSort3 = QuizQuestion::create([
            'quiz_id' => $quizColors->id, 'quiz_type_id' => $sortTypeId,
            'prompt' => 'Sort by color! Red or Blue?',
            'points' => 4, 'sort_order' => 2,
            'hint' => 'Look at each thing. Is it red or blue?',
            'explanation' => 'Strawberries and apples are red. The ocean and sky are blue!',
            'metadata' => ['categories' => ['🔴 Red', '🔵 Blue']],
        ]);
        $this->addOption($qSort3->id, 'text', '🍓 Strawberry', false, 0, '🔴 Red');
        $this->addOption($qSort3->id, 'text', '🌊 Ocean', false, 1, '🔵 Blue');
        $this->addOption($qSort3->id, 'text', '🍎 Apple', false, 2, '🔴 Red');
        $this->addOption($qSort3->id, 'text', '👖 Jeans', false, 3, '🔵 Blue');

        // ── LINK LESSONS TO ADVENTURE WORLDS ──────────────────────────
        // This is what makes missions appear on the Adventure Map!
        $this->linkLessonsToWorlds([
            // Whispering Forest — alphabet adventures
            ['world' => 'whispering-forest', 'lesson' => $lessonA, 'story' => '🔍 Find the Hidden Apple!', 'order' => 1],
            ['world' => 'whispering-forest', 'lesson' => $lessonB, 'story' => '🐝 Buzzing for Letter B!', 'order' => 2],
            ['world' => 'whispering-forest', 'lesson' => $lessonC, 'story' => '🐱 Curvy C Adventure!', 'order' => 3],
            // Safari Plains — animals
            ['world' => 'safari-plains', 'lesson' => $lessonFarm, 'story' => '🐄 Meet the Farm Friends!', 'order' => 1],
            // Ocean Cove — colors (blue)
            ['world' => 'ocean-cove', 'lesson' => $lessonBlue, 'story' => '🌊 Deep Blue Sea Exploration!', 'order' => 1],
            ['world' => 'ocean-cove', 'lesson' => $lessonRed, 'story' => '🦀 The Crimson Treasure!', 'order' => 2],
            // Castle of Discovery — shapes
            ['world' => 'castle-of-discovery', 'lesson' => $lessonShapes, 'story' => '🏰 The Castle of Shapes!', 'order' => 1],
            // Star Valley — numbers (counting)
            ['world' => 'star-valley', 'lesson' => $lesson1, 'story' => '⭐ Five Sparkly Stars!', 'order' => 1],
            ['world' => 'star-valley', 'lesson' => $lesson2, 'story' => '🚀 Blast Off to 10!', 'order' => 2],
        ]);

        echo "✅ Seeded: 5 subjects, 5 topics, 9 lessons, 6 quizzes, 12 questions with options!\n";
        echo "🗺️  Linked 9 lessons across 5 adventure worlds (missions on map)!\n";
    }

    private function createSubject(string $name, string $icon, string $color, string $desc): Subject
    {
        return Subject::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $desc,
            'icon' => $icon,
            'color' => $color,
            'status' => 'published',
            'sort_order' => 0,
            'created_by' => 1,
        ]);
    }

    private function createTopic(int $subjectId, string $name, string $icon, int $adminId): Topic
    {
        return Topic::create([
            'subject_id' => $subjectId,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => "Fun activities for {$name}",
            'icon' => $icon,
            'status' => 'published',
            'sort_order' => 0,
            'created_by' => $adminId,
        ]);
    }

    private function createLesson(int $topicId, string $title, int $adminId, string $summary, int $duration): Lesson
    {
        return Lesson::create([
            'topic_id' => $topicId,
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => $summary,
            'content' => "<p>{$summary}</p>",
            'content_type' => 'text',
            'duration_minutes' => $duration,
            'status' => 'published',
            'sort_order' => 0,
            'created_by' => $adminId,
            'published_at' => now(),
        ]);
    }

    private function createQuiz(int $lessonId, string $title, int $adminId): Quiz
    {
        return Quiz::create([
            'lesson_id' => $lessonId,
            'title' => $title,
            'instructions' => 'Tap the best answer. You can do it!',
            'pass_threshold_percent' => 70,
            'max_attempts' => 3,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'status' => 'published',
            'sort_order' => 0,
            'created_by' => $adminId,
        ]);
    }

    /**
     * Link lessons to adventure worlds via the world_lessons pivot.
     * This is what makes lessons appear as "missions" on the Adventure Map.
     */
    private function linkLessonsToWorlds(array $links): void
    {
        foreach ($links as $link) {
            $world = AdventureWorld::where('slug', $link['world'])->first();
            if (! $world || ! $link['lesson']) {
                continue;
            }

            WorldLesson::create([
                'adventure_world_id' => $world->id,
                'lesson_id' => $link['lesson']->id,
                'story_title' => $link['story'],
                'sort_order' => $link['order'],
            ]);
        }
    }

    private function addOption(int $questionId, string $type, string $value, bool $isCorrect, int $order, ?string $matchKey = null): void
    {
        $data = [
            'question_id' => $questionId,
            'content_type' => $type,
            'is_correct' => $isCorrect,
            'sort_order' => $order,
        ];

        if ($type === 'text') {
            $data['text_value'] = $value;
        } elseif ($type === 'image') {
            $data['image_url'] = $value;
        } else {
            $data['audio_url'] = $value;
        }

        if ($matchKey) {
            $data['match_key'] = $matchKey;
        }

        QuestionOption::create($data);
    }
}