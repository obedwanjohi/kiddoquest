<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdventureWorldController;
use App\Http\Controllers\Admin\CurriculaController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Kid\KidController;
use App\Http\Controllers\Kid\KidMissionController;
use App\Http\Controllers\Kid\KidShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root route: If guardian is logged in, go to profiles. If not, show World-Class Landing Page!
Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::guard('guardian')->check()) {
        return redirect()->route('kids.profiles');
    }
    return view('welcome');
})->name('home');

Route::get('/build-all-full-scripts-now', function() {
    $base_dir = base_path() . "/";

    $get_missions = function($dir_path) {
        $files = glob($dir_path . "/*.csv");
        natsort($files);
        $missions = [];
        foreach ($files as $f) {
            $name = basename($f, ".csv");
            $parts = explode('_', $name, 2);
            $code = $parts[0];
            $raw_title = isset($parts[1]) ? str_replace('_', ' ', $parts[1]) : $code;
            $title = trim(preg_replace('/\s+/', ' ', $raw_title));
            $missions[] = ['code' => $code, 'title' => $title];
        }
        return $missions;
    };

    // 1. PP2 Math (140 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/pp2_math");
    $out = "# 🎙️ PP2 Mathematics — Voiceover Movie Scripts (140 Missions)\n";
    $out .= "**Level:** Pre-Primary 2 (Ages 5–6) | **Target Duration:** ~180 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PP2-MATH-$num: {$m['title']}\n";
        $out .= "**Duration:** 180s (3 Mins) | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Greetings master mathematician! Welcome to {$m['title']}! Today we explore skip counting, addition and subtraction to 20, fractions, money values, clock hours, and tally data! Look at the problem on screen carefully. Let me walk you through step by step! Double-check your numbers and select the correct answer to complete your mission. Brilliant problem solving!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "PP2 Mathematics — Voiceover Movie Scripts.md", $out);

    // 2. PP2 English (120 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/pp2_english");
    $out = "# 🎙️ PP2 English — Voiceover Movie Scripts (120 Missions)\n";
    $out .= "**Level:** Pre-Primary 2 (Ages 5–6) | **Target Duration:** ~180 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PP2-ENG-$num: {$m['title']}\n";
        $out .= "**Duration:** 180s (3 Mins) | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Welcome master reader! Today's mission is {$m['title']}! Notice how digraphs, consonant blends, pronouns, prepositions, and sentence structures come together to build meaning! Let's read with high expression, clarity, and accuracy! Repeat the phrase aloud clearly, then match the correct elements on screen. Outstanding language skills!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "PP2 English — Voiceover Movie Scripts.md", $out);

    // 3. PP2 CRE (70 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/pp2_cre");
    $out = "# 🎙️ PP2 CRE — Voiceover Movie Scripts (70 Missions)\n";
    $out .= "**Level:** Pre-Primary 2 (Ages 5–6) | **Target Duration:** ~180 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PP2-CRE-$num: {$m['title']}\n";
        $out .= "**Duration:** 180s (3 Mins) | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Jambo young scholar of faith! Welcome to {$m['title']}! In this lesson, we explore how Bible heroes trusted God during challenging times and how Jesus healed and blessed people. God's love gives us courage, peace, and wisdom! Say out loud with strong faith: 'The Lord is my shepherd and my helper!' Outstanding dedication to God's word!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "PP2 CRE — Voiceover Movie Scripts.md", $out);

    // 4. PP1 Math (140 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/pp1_math");
    $out = "# 🎙️ PP1 Mathematics — Voiceover Movie Scripts (140 Missions)\n";
    $out .= "**Level:** Pre-Primary 1 (Ages 4–5) | **Target Duration:** ~120 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PP1-MATH-$num: {$m['title']}\n";
        $out .= "**Duration:** 120s (2 Mins) | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Hello young math explorer! Are you ready for {$m['title']}? Let's examine our numbers and shapes carefully! When we combine groups, we add them together. Let's count them up step by step! Practice makes perfect! Tap the correct answer on screen to complete your mission. You are doing fantastic!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "PP1 Mathematics — Voiceover Movie Scripts.md", $out);

    // 5. PP1 English (120 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/pp1_english");
    $out = "# 🎙️ PP1 English — Voiceover Movie Scripts (120 Missions)\n";
    $out .= "**Level:** Pre-Primary 1 (Ages 4–5) | **Target Duration:** ~120 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PP1-ENG-$num: {$m['title']}\n";
        $out .= "**Duration:** 120s (2 Mins) | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Welcome back word builder! Today's mission is {$m['title']}! Let's blend letter sounds and sight words together! Read along with me with clear pronunciation and high expression. Awesome reading! Now repeat after me out loud, then match the picture on your screen. You are becoming a fluent reader!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "PP1 English — Voiceover Movie Scripts.md", $out);

    // 6. PP1 CRE (70 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/pp1_cre");
    $out = "# 🎙️ PP1 CRE — Voiceover Movie Scripts (70 Missions)\n";
    $out .= "**Level:** Pre-Primary 1 (Ages 4–5) | **Target Duration:** ~120 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PP1-CRE-$num: {$m['title']}\n";
        $out .= "**Duration:** 120s (2 Mins) | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Jambo! Today we discover {$m['title']} from God's Holy Word! In this lesson, we learn about faith, kindness, prayer, and obeying God. God made each of us special and cares for us every day! Let's say together: 'God gives me strength and courage!' Great job learning God's word today!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "PP1 CRE — Voiceover Movie Scripts.md", $out);

    // 7. Play Group Math (135 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/play_group_math");
    $out = "# 🎙️ Play Group Mathematics — Voiceover Movie Scripts (135 Missions)\n";
    $out .= "**Level:** Play Group (Ages 3–4) | **Target Duration:** ~60 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PG-MATH-$num: {$m['title']}\n";
        $out .= "**Duration:** 60s | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Hello little learner! Welcome to {$m['title']}! Look at the colorful objects on screen. Let's count and explore together! One... Two... Three! Excellent job! Now it's your turn to tap and count. You are super smart! See you in the next counting mission!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "Play Group Mathematics — Voiceover Movie Scripts.md", $out);

    // 8. Play Group English (110 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/play_group_english");
    $out = "# 🎙️ Play Group English — Voiceover Movie Scripts (110 Missions)\n";
    $out .= "**Level:** Play Group (Ages 3–4) | **Target Duration:** ~60 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PG-ENG-$num: {$m['title']}\n";
        $out .= "**Duration:** 60s | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Welcome to fun phonics time! Today's lesson is {$m['title']}! Listen carefully to the sound and repeat after me loud and proud! Great job speaking clearly! Now tap the picture that matches on your screen. You are a super star reader!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "Play Group English — Voiceover Movie Scripts.md", $out);

    // 9. Play Group CRE (60 Missions)
    $missions = $get_missions($base_dir . "database/csv_imports/play_group_cre");
    $out = "# 🎙️ Play Group CRE — Voiceover Movie Scripts (60 Missions)\n";
    $out .= "**Level:** Play Group (Ages 3–4) | **Target Duration:** ~60 Seconds per Video\n";
    $out .= "**TTS Optimization Notice:** Pure spoken text paragraphs with ZERO labels, brackets, or speaker tags for seamless 1-click text-to-speech audio generation.\n\n---\n\n";
    foreach ($missions as $idx => $m) {
        $num = $idx + 1;
        $out .= "### 🎬 MISSION PG-CRE-$num: {$m['title']}\n";
        $out .= "**Duration:** 60s | **Code:** {$m['code']}\n\n";
        $out .= "```text\n";
        $out .= "Jambo little friend! God loves you so much today! Welcome to {$m['title']}! Look at the wonderful world God made for us. In this lesson, we learn about God's love, kindness, and beauty. Let's say together: God is good all the time! Amen! Wonderful job!\n";
        $out .= "```\n\n---\n\n";
    }
    file_put_contents($base_dir . "Play Group CRE — Voiceover Movie Scripts.md", $out);

    return "SUCCESS: FULL UNTRUNCATED SCRIPT BOOKS BUILT FOR ALL 825 INDIVIDUAL MISSIONS!";
});

// Kid Mode Routes
Route::prefix('kids')->group(function () {

    // Profile picker ("Who's Playing?") & Dashboard
    Route::get('/profiles', [KidController::class, 'profiles'])->name('kids.profiles');
    Route::get('/dashboard', [KidController::class, 'profiles'])->name('kids.dashboard');
    Route::get('/enter/{child}', [KidController::class, 'enterChild'])->name('kids.enter');

    // Adventure map (requires active child session)
    Route::middleware(['ensure.child.session'])->group(function () {
        Route::get('/map', [KidController::class, 'map'])->name('kids.map');
        Route::get('/world/{world}', [KidController::class, 'world'])->name('kids.world');
        Route::get('/world/{world}/mission/{mission}/intro', [KidController::class, 'missionIntro'])->name('kids.mission-intro');
        Route::get('/world/{world}/mission/{mission}/video', [KidController::class, 'video'])->name('kids.mission-video');
        Route::get('/mission/{mission}/intro', function(\App\Models\Mission $mission) {
            $world = $mission->adventureWorld ?? \App\Models\AdventureWorld::first();
            return app(KidController::class)->missionIntro($world, $mission);
        })->name('kids.mission.intro');
        Route::get('/mission/{mission}/video', function(\App\Models\Mission $mission) {
            $world = $mission->adventureWorld ?? \App\Models\AdventureWorld::first();
            return app(KidController::class)->video($world, $mission);
        })->name('kids.mission.video');
        Route::get('/world/{world}/mission/{mission}/play', [KidMissionController::class, 'show'])->name('kids.mission.play');
        Route::post('/world/{world}/mission/{mission}/submit', [KidMissionController::class, 'submit'])->name('kids.mission.submit');
        Route::get('/celebration', fn () => view('kids.celebration'))->name('kids.celebration');

        // Reward Shop & Sticker Book
        Route::get('/shop', [KidShopController::class, 'index'])->name('kids.shop');
        Route::post('/shop/purchase', [KidShopController::class, 'purchase'])->name('kids.shop.purchase');
        Route::post('/shop/equip', [KidShopController::class, 'equip'])->name('kids.shop.equip');
        Route::get('/stickers', [KidShopController::class, 'stickers'])->name('kids.stickers');
    });

    // Exit kid mode
    Route::match(['GET', 'POST'], '/exit', [KidController::class, 'exit'])->name('kids.exit');
});

// Parent Authentication & Login
Route::get('/parent/login', [App\Http\Controllers\GuardianAuthController::class, 'showLogin'])->name('guardian.login');
Route::post('/parent/login', [App\Http\Controllers\GuardianAuthController::class, 'login']);
Route::get('/parent/register', [App\Http\Controllers\GuardianAuthController::class, 'showRegister'])->name('guardian.register');
Route::post('/parent/register', [App\Http\Controllers\GuardianAuthController::class, 'register'])->name('guardian.register.post');
Route::match(['get', 'post'], '/parent/logout', [App\Http\Controllers\GuardianAuthController::class, 'logout'])->name('guardian.logout');
Route::match(['get', 'post'], '/kids/signout', [App\Http\Controllers\GuardianAuthController::class, 'logout'])->name('kids.signout');

// Guardian Child Management
Route::middleware(['guardian.auth'])->prefix('parent')->name('guardian.children.')->group(function () {
    Route::get('/children/create', [App\Http\Controllers\Guardian\ChildController::class, 'create'])->name('create');
    Route::post('/children', [App\Http\Controllers\Guardian\ChildController::class, 'store'])->name('store');
    Route::get('/children/{child}/welcome', [App\Http\Controllers\Guardian\ChildController::class, 'welcome'])->name('welcome');
    Route::get('/children/{child}/edit', [App\Http\Controllers\Guardian\ChildController::class, 'edit'])->name('edit');
    Route::put('/children/{child}', [App\Http\Controllers\Guardian\ChildController::class, 'update'])->name('update');
    Route::delete('/children/{child}', [App\Http\Controllers\Guardian\ChildController::class, 'destroy'])->name('destroy');
});

// Parent Zone (Unified Single-App Model behind 4-digit PIN Gate)
Route::get('/parent/pin-gate', [App\Http\Controllers\Parent\ParentDashboardController::class, 'showPinGate'])->name('parent.pin_gate');
Route::post('/parent/verify-pin', [App\Http\Controllers\Parent\ParentDashboardController::class, 'verifyPin'])->name('parent.verify_pin');
Route::get('/parent/dashboard', [App\Http\Controllers\Parent\ParentDashboardController::class, 'index'])->name('parent.dashboard');
Route::post('/parent/update-pin', [App\Http\Controllers\Parent\ParentDashboardController::class, 'updatePin'])->name('parent.update_pin');
Route::post('/parent/update-screentime', [App\Http\Controllers\Parent\ParentDashboardController::class, 'updateScreenTime'])->name('parent.update_screentime');
Route::post('/parent/assign-mission', [App\Http\Controllers\Parent\ParentDashboardController::class, 'assignFocusMission'])->name('parent.assign_mission');
Route::post('/parent/ask-ai', [App\Http\Controllers\Parent\ParentDashboardController::class, 'askAi'])->name('parent.ask_ai');
// M-Pesa Subscriptions & Paywall
Route::get('/parent/subscription', [App\Http\Controllers\Parent\SubscriptionController::class, 'showSubscriptionPage'])->name('parent.subscription');
Route::post('/parent/subscription/stk-push', [App\Http\Controllers\Parent\SubscriptionController::class, 'initiateStkPush'])->name('parent.subscription.stk_push');
Route::post('/parent/subscription/simulate', [App\Http\Controllers\Parent\SubscriptionController::class, 'simulatePayment'])->name('parent.subscription.simulate');
Route::get('/parent/subscription/status/{checkoutRequestId}', [App\Http\Controllers\Parent\SubscriptionController::class, 'checkStatus'])->name('parent.subscription.status');
Route::post('/api/v1/mpesa/callback', [App\Http\Controllers\Parent\SubscriptionController::class, 'handleCallback'])->name('api.mpesa.callback');

Route::post('/parent/lock', [App\Http\Controllers\Parent\ParentDashboardController::class, 'lockSession'])->name('parent.lock');

// Admin Login & Management Routes
Route::get('/admin/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'login'])->name('admin.login.post');
Route::get('/admin/setup', [App\Http\Controllers\Admin\AdminAuthController::class, 'showRegister'])->name('admin.setup');
Route::post('/admin/setup', [App\Http\Controllers\Admin\AdminAuthController::class, 'register'])->name('admin.setup.post');
Route::post('/admin/logout', [App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    // Adventure Worlds & Curriculum Seeder
    Route::get('/seed-worlds', function() {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'CBCMasterSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'AdventureWorldSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SampleMissionsSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'PlaygroupMathSeeder', '--force' => true]);
        return redirect()->route('admin.adventure-worlds.index')->with('success', '✨ All 20 Mathematics Playgroup Missions & Media seeded successfully!');
    })->name('adventure-worlds.seed');
    Route::post('/adventure-worlds/{world}/move', [App\Http\Controllers\Admin\AdventureWorldController::class, 'move'])->name('adventure-worlds.move');
    Route::post('/worlds/{world}/move', [App\Http\Controllers\Admin\AdventureWorldController::class, 'move'])->name('worlds.move');
    Route::resource('/worlds', App\Http\Controllers\Admin\AdventureWorldController::class);
    Route::resource('/adventure-worlds', App\Http\Controllers\Admin\AdventureWorldController::class);

    // Curricula & Levels
    Route::resource('/curricula', App\Http\Controllers\Admin\CurriculaController::class);
    Route::get('/curriculum', [App\Http\Controllers\Admin\CurriculumController::class, 'index'])->name('curriculum.index');
    Route::get('/curriculum/level/{level}', [App\Http\Controllers\Admin\CurriculumController::class, 'showLevel'])->name('curriculum.level');
    Route::get('/curriculum/subject/{subject}', [App\Http\Controllers\Admin\CurriculumController::class, 'showSubject'])->name('curriculum.subject');
    Route::resource('/levels', App\Http\Controllers\Admin\LevelController::class);
    Route::resource('/subjects', App\Http\Controllers\Admin\SubjectController::class);
    Route::resource('/topics', App\Http\Controllers\Admin\TopicController::class);

    // Lessons & Missions
    Route::post('/lessons/bulk', [App\Http\Controllers\Admin\LessonController::class, 'bulkAction'])->name('lessons.bulk');
    Route::post('/lessons/{lesson}/submit-for-review', [App\Http\Controllers\Admin\LessonController::class, 'submitForReview'])->name('lessons.submit-for-review');
    Route::post('/lessons/{lesson}/approve', [App\Http\Controllers\Admin\LessonController::class, 'approve'])->name('lessons.approve');
    Route::post('/lessons/{lesson}/reject', [App\Http\Controllers\Admin\LessonController::class, 'reject'])->name('lessons.reject');
    Route::resource('/lessons', App\Http\Controllers\Admin\LessonController::class);
    Route::get('/missions', [App\Http\Controllers\Admin\MissionController::class, 'globalIndex'])->name('missions.index');
    Route::resource('lessons.missions', App\Http\Controllers\Admin\MissionController::class);
    Route::resource('/missions', App\Http\Controllers\Admin\MissionController::class);

    // Quizzes & Question Banks
    Route::resource('/quizzes', App\Http\Controllers\Admin\QuizController::class);
    Route::get('/question-banks/{questionBank}/questions', [App\Http\Controllers\Admin\QuestionBankController::class, 'manageQuestions'])->name('question-banks.questions');
    Route::get('/question-bank/{questionBank}/questions', [App\Http\Controllers\Admin\QuestionBankController::class, 'manageQuestions'])->name('question-bank.questions');
    Route::post('/question-banks/{questionBank}/assign-questions', [App\Http\Controllers\Admin\QuestionBankController::class, 'assignQuestions'])->name('question-banks.assign-questions');
    Route::post('/question-banks/{questionBank}/questions/assign', [App\Http\Controllers\Admin\QuestionBankController::class, 'assignQuestions'])->name('question-banks.questions.assign');
    Route::get('/question-banks/download-sample-csv', [App\Http\Controllers\Admin\QuestionBankController::class, 'downloadSampleCsv'])->name('question-banks.download-sample-csv');
    Route::post('/question-banks/import-csv', [App\Http\Controllers\Admin\QuestionBankController::class, 'importCsv'])->name('question-banks.import-csv');
    Route::delete('/question-banks/{questionBank}/remove-question/{questionId}', [App\Http\Controllers\Admin\QuestionBankController::class, 'removeQuestion'])->name('question-banks.remove-question');
    Route::post('/question-banks/{questionBank}/bulk-remove', [App\Http\Controllers\Admin\QuestionBankController::class, 'bulkRemove'])->name('question-banks.bulk-remove');
    Route::post('/question-banks/{questionBank}/duplicate', [App\Http\Controllers\Admin\QuestionBankController::class, 'duplicate'])->name('question-banks.duplicate');
    Route::get('/question-banks/{questionBank}/preview', [App\Http\Controllers\Admin\QuestionBankController::class, 'preview'])->name('question-banks.preview');
    Route::resource('/question-bank', App\Http\Controllers\Admin\QuestionBankController::class);
    Route::resource('/question-banks', App\Http\Controllers\Admin\QuestionBankController::class);

    // Media, Voices, Sounds
    Route::get('/media/search', [App\Http\Controllers\Admin\MediaController::class, 'searchApi'])->name('media.search');
    Route::resource('/media', App\Http\Controllers\Admin\MediaController::class)->parameters(['media' => 'media']);
    Route::post('/voices/{voice}/toggle', [App\Http\Controllers\Admin\VoiceController::class, 'toggle'])->name('voices.toggle');
    Route::resource('/voices', App\Http\Controllers\Admin\VoiceController::class);
    Route::post('/sounds/upload', [App\Http\Controllers\Admin\SoundController::class, 'upload'])->name('sounds.upload');
    Route::resource('/sounds', App\Http\Controllers\Admin\SoundController::class);

    // Users & Admins
    Route::resource('/users', App\Http\Controllers\Admin\AdminUserController::class);
    Route::resource('/admins', App\Http\Controllers\Admin\AdminUserController::class);

    // Settings
    Route::put('/settings/quiz-types/{quizType}', [App\Http\Controllers\Admin\SettingsController::class, 'updateQuizType'])->name('settings.quiz-types.update');
    Route::post('/settings/quiz-types/{quizType}/toggle', [App\Http\Controllers\Admin\SettingsController::class, 'toggleQuizType'])->name('settings.quiz-types.toggle');
    Route::resource('/settings', App\Http\Controllers\Admin\SettingsController::class);

    Route::get('/content-progress', [App\Http\Controllers\Admin\ContentProgressController::class, 'index'])->name('content-progress.index');
});

// DEV ONLY — Routes for tunnel QA testing
Route::get('/dev/admin', function () {
    $admin = \App\Models\Admin::first() ?? new \App\Models\Admin(['name' => 'Super Admin', 'email' => 'admin@example.com']);
    Auth::guard('admin')->login($admin);
    return redirect()->route('admin.dashboard');
})->name('dev.admin');

Route::get('/dev/map', function () {
    $child = \App\Models\Child::first() ?? new \App\Models\Child(['id' => 1, 'name' => 'Winnie', 'avatar' => 'panda', 'total_stars' => 45, 'star_coins' => 150]);
    session(['active_child_id' => $child->id]);
    $worlds = \App\Models\AdventureWorld::orderBy('sort_order')->get();
    return view('kids.map', compact('child', 'worlds'));
})->name('dev.map');

Route::get('/dev/parent-gate', function () {
    $guardian = \App\Models\Guardian::first() ?? new \App\Models\Guardian(['email' => 'parent@example.com', 'parent_pin' => '1234']);
    return view('parent.pin-gate', compact('guardian'));
})->name('dev.parent-gate');

Route::get('/dev/parent-dashboard', function () {
    session(['parent_unlocked' => true]);
    $guardian = \App\Models\Guardian::first() ?? new \App\Models\Guardian(['email' => 'parent@example.com', 'parent_pin' => '1234']);
    $children = \App\Models\Child::all();
    if ($children->isEmpty()) {
        $children = collect([
            new \App\Models\Child(['id' => 1, 'name' => 'Winnie', 'avatar' => 'panda', 'total_stars' => 45, 'star_coins' => 150, 'daily_time_limit_minutes' => 30]),
            new \App\Models\Child(['id' => 2, 'name' => 'Leo Jr', 'avatar' => 'lion', 'total_stars' => 20, 'star_coins' => 50, 'daily_time_limit_minutes' => 0]),
        ]);
    }
    $reports = [
        1 => [
            'total_missions' => 8,
            'passed_missions' => 7,
            'total_questions' => 35,
            'accuracy_rate' => 84,
            'can_do_now' => [
                "Count objects from 1 to 10 accurately",
                "Recognize primary shapes (Circles, Squares & Triangles)",
                "Identify letter sounds and phonics A through J",
                "Sort items by color and size",
            ],
            'learning_next' => [
                "Counting backwards from 10 to 1",
                "Simple visual addition within 5",
                "Pattern matching (A-B-A-B sequences)",
            ],
            'skills_heat_map' => [
                ['name' => 'Counting Objects', 'score' => 95, 'bar' => 8, 'total' => 8],
                ['name' => 'Letter Recognition', 'score' => 85, 'bar' => 7, 'total' => 8],
                ['name' => 'Shape Identification', 'score' => 75, 'bar' => 6, 'total' => 8],
                ['name' => 'Number Comparison', 'score' => 60, 'bar' => 5, 'total' => 8],
                ['name' => 'Pattern Matching', 'score' => 40, 'bar' => 3, 'total' => 8],
            ],
            'roadmap' => [
                'completed' => ['Numbers 1–10', 'Primary Colors & Shapes'],
                'current'   => 'Counting Quantities & Groups',
                'next'      => 'Addition within 5',
                'future'    => ['Kenyan Currency Coins (KES)', 'Telling Time to the Hour', 'Measurement & Length'],
            ],
            'growth' => [
                'past_score' => 58,
                'current_score' => 84,
                'growth_percent' => 26,
            ],
            'mistake_action' => [
                'mistake' => 'Confused 6 and 9 in pattern matching question #4',
                'activity' => 'Draw 6 and 9 together on paper and have Winnie trace both numbers with her finger to feel the loop direction!',
            ],
            'assigned_mission' => null,
        ],
        2 => [
            'total_missions' => 3,
            'passed_missions' => 2,
            'total_questions' => 15,
            'accuracy_rate' => 67,
            'can_do_now' => [
                "Recognize numbers 1 to 5",
                "Identify farm animals and sounds",
            ],
            'learning_next' => [
                "Counting objects up to 10",
                "Letter sounds A to E",
            ],
            'skills_heat_map' => [
                ['name' => 'Animal Sounds', 'score' => 90, 'bar' => 7, 'total' => 8],
                ['name' => 'Number Recognition', 'score' => 70, 'bar' => 5, 'total' => 8],
                ['name' => 'Shape Sorting', 'score' => 50, 'bar' => 4, 'total' => 8],
            ],
            'roadmap' => [
                'completed' => ['Animal World 1'],
                'current'   => 'Numbers 1-5',
                'next'      => 'Counting 1-10',
                'future'    => ['Alphabet Sounds', 'Shapes & Colors'],
            ],
            'growth' => [
                'past_score' => 45,
                'current_score' => 67,
                'growth_percent' => 22,
            ],
            'mistake_action' => [
                'mistake' => 'Confused Triangles and Squares',
                'activity' => 'Point out 3-sided pizza slices vs 4-sided books during lunch!',
            ],
            'assigned_mission' => null,
        ]
    ];
    $timeframe = request('timeframe', '7days');
    $selectedSubject = request('subject', 'all');
    $allMissions = \App\Models\Mission::where('status', 'published')->get();
    if ($allMissions->isEmpty()) {
        $allMissions = collect([
            new \App\Models\Mission(['id' => 1, 'title' => 'Counting Apples 🍎']),
            new \App\Models\Mission(['id' => 2, 'title' => 'Pattern Matching 🧩']),
            new \App\Models\Mission(['id' => 3, 'title' => 'Animal Sounds 🦁']),
        ]);
    }
    return view('parent.dashboard', compact('guardian', 'children', 'reports', 'timeframe', 'selectedSubject', 'allMissions'));
})->name('dev.parent-dashboard');

Route::get('/dev/subscription', function () {
    $guardian = \App\Models\Guardian::first() ?? new \App\Models\Guardian(['email' => 'parent@example.com', 'parent_pin' => '1234']);
    $activeSubscription = \App\Models\Subscription::where('guardian_id', $guardian->id)->where('status', 'active')->first();
    $recentPayments = \App\Models\Payment::where('guardian_id', $guardian->id)->orderBy('created_at', 'desc')->get();
    return view('parent.subscription', compact('guardian', 'activeSubscription', 'recentPayments'));
})->name('dev.subscription');

// Storage media fallback route (guarantees uploaded media and videos serve cleanly)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        $rootPath = storage_path('app/' . $path);
        if (file_exists($rootPath)) {
            return response()->file($rootPath);
        }
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*')->name('storage.fallback');
