<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\Mission;
use App\Models\QuizQuestion;
use App\Services\Content\QuestionNormalizer;
use App\Services\Learning\MissionCompletionService;
use App\Services\Learning\MissionScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Plays real missions for a child, spread over recent days.
 *
 * This exists so the parent dashboard and the app can be looked at with a
 * plausible history behind them without anybody sitting through twenty
 * missions. It writes through the same service the sync endpoint uses, so the
 * numbers it produces are the numbers a real child would have produced — no
 * hand-written rows, nothing the projections would disagree with.
 *
 * Development only: it refuses to run in production.
 */
class PlayDemoMissions extends Command
{
    protected $signature = 'kiddoquest:demo-play
        {child : The child id to play as}
        {--days=5 : Spread the play over this many recent days}
        {--missions=2 : Missions per day}
        {--accuracy=80 : Percentage of questions answered correctly}';

    protected $description = 'Play missions for a child so the dashboard has real history to show (development only).';

    public function handle(MissionScoringService $scoring, MissionCompletionService $completion): int
    {
        if (app()->environment('production')) {
            $this->error('This command is for development data only.');

            return self::FAILURE;
        }

        $child = Child::find((int) $this->argument('child'));

        if (! $child) {
            $this->error('No such child.');

            return self::FAILURE;
        }

        $days = max(1, (int) $this->option('days'));
        $perDay = max(1, (int) $this->option('missions'));
        $accuracy = max(0, min(100, (int) $this->option('accuracy')));

        // Only missions whose questions can actually be answered from here.
        $missions = Mission::whereNotNull('question_bank_id')
            ->with('questionBank')
            ->inRandomOrder()
            ->limit($days * $perDay * 3)
            ->get()
            ->filter(fn (Mission $mission) => $this->bankQuestions($mission)->isNotEmpty())
            ->values()
            ->take($days * $perDay);

        if ($missions->isEmpty()) {
            $this->error('No missions with questions are seeded.');

            return self::FAILURE;
        }

        $played = 0;

        foreach (range($days - 1, 0) as $daysAgo) {
            for ($i = 0; $i < $perDay; $i++) {
                $mission = $missions[$played % $missions->count()];
                $completedAt = now()->subDays($daysAgo)->setTime(17, 30)->addMinutes($i * 20);

                $answers = $this->answersFor($mission, $scoring->sessionCap($mission), $accuracy);

                if ($answers === []) {
                    continue;
                }

                $result = $scoring->scoreMission($mission, $answers);

                $completion->apply($child, $mission, $result, [
                    'event_id'     => (string) Str::uuid(),
                    'answers'      => $answers,
                    'time_spent'   => random_int(150, 400),
                    'completed_at' => $completedAt,
                    'source'       => 'demo',
                ]);

                $this->line(sprintf(
                    '%s  %-40s %d/%d  %d★',
                    $completedAt->toDateString(),
                    Str::limit($mission->title ?? "Mission {$mission->id}", 38),
                    $result->score,
                    $result->total,
                    $result->stars
                ));

                $played++;
            }
        }

        $fresh = $child->fresh();
        $this->info("Played {$played} missions. {$fresh->name} now has {$fresh->total_stars} stars, {$fresh->star_coins} coins, streak {$fresh->streak_days}.");

        return self::SUCCESS;
    }

    /**
     * Answer a mission's questions, getting roughly the requested share right.
     *
     * A wrong answer is a real wrong option rather than a blank, so the mistakes
     * list on the dashboard has something truthful in it.
     */
    protected function answersFor(Mission $mission, int $cap, int $accuracy): array
    {
        $questions = $this->bankQuestions($mission)->shuffle()->take($cap);

        $answers = [];

        foreach ($questions as $question) {
            $definition = QuestionNormalizer::fromModel($question);
            $options = collect($definition['options'] ?? []);

            if ($options->isEmpty()) {
                // Tracing and speak-repeat carry no options; doing them is passing.
                $answers[] = ['question_id' => (int) $question->id, 'response' => ['done' => true]];

                continue;
            }

            $wantCorrect = random_int(1, 100) <= $accuracy;
            $correct = $options->firstWhere(fn ($option) => ! empty($option['is_correct']));
            $wrong = $options->first(fn ($option) => empty($option['is_correct']));
            $chosen = ($wantCorrect ? $correct : $wrong) ?? $correct ?? $options->first();

            $answers[] = [
                'question_id' => (int) $question->id,
                'response'    => ['option_id' => (int) ($chosen['id'] ?? 0)],
            ];
        }

        return $answers;
    }

    /**
     * The mission's bank, drawn the way the content pack draws it: an explicitly
     * assigned set if there is one, otherwise everything the bank owns.
     *
     * @return \Illuminate\Support\Collection<int,QuizQuestion>
     */
    protected function bankQuestions(Mission $mission): \Illuminate\Support\Collection
    {
        $bank = $mission->questionBank;

        if (! $bank) {
            return collect();
        }

        return $bank->assignedQuestions()->exists()
            ? $bank->assignedQuestions()->with(['options', 'quizType'])->get()
            : $bank->questions()->with(['options', 'quizType'])->get();
    }
}
