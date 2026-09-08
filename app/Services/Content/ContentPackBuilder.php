<?php

namespace App\Services\Content;

use App\Http\Middleware\EnsureActiveSubscription;
use App\Models\AdventureWorld;
use App\Models\ContentPack;
use App\Models\Mission;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Exports one adventure world as a self-contained, versioned pack.
 *
 * The pack holds the whole question bank, not a drawn session, because the app
 * runs the same balanced draw and seven-day exclusion filter locally while the
 * child is offline. Media is referenced by a stable path key and resolved
 * through the media[] table at the bottom of the pack, so the same file shared
 * by twenty questions is downloaded once.
 */
class ContentPackBuilder
{
    /** @var array<string,array> */
    protected array $media = [];

    protected array $missing = [];

    public function __construct(protected MediaResolver $resolver)
    {
    }

    /**
     * Build and publish the next version of a world's pack.
     */
    public function publish(AdventureWorld $world): ContentPack
    {
        $this->media = [];
        $this->missing = [];

        $world->loadMissing(['subject.level']);

        $packId = $this->packId($world);
        $version = ((int) ContentPack::where('pack_id', $packId)->max('version')) + 1;

        $missions = $this->missions($world);

        $document = [
            'pack_id'      => $packId,
            'version'      => $version,
            'generated_at' => now()->toIso8601String(),
            'level'        => $this->levelCode($world),
            'subject'      => [
                'id'       => $world->subject_id ? (int) $world->subject_id : null,
                'code'     => $this->subjectCode($world),
                'name'     => $world->subject->name ?? 'Adventures',
                'category' => $world->subject_category,
            ],
            'world' => [
                'id'          => (int) $world->id,
                'slug'        => $world->slug,
                'name'        => $world->name,
                'icon'        => $world->icon,
                'description' => $world->description,
                'theme_color' => $world->theme_color,
                'is_free'     => EnsureActiveSubscription::isFreeWorld($world),
                'sort_order'  => (int) $world->sort_order,
            ],
            'missions' => $missions,
            'media'    => array_values($this->media),
        ];

        $json = json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $path = trim((string) config('kiddoquest.content.root', 'packs'), '/') . "/{$packId}/v{$version}/pack.json";

        $disk = Storage::disk(config('kiddoquest.content.disk', 'public'));
        $disk->put($path, $json);

        $pack = ContentPack::create([
            'pack_id'        => $packId,
            'version'        => $version,
            'world_id'       => $world->id,
            'subject_id'     => $world->subject_id,
            'level_code'     => $document['level'],
            'subject_code'   => $document['subject']['code'],
            'world_slug'     => $world->slug,
            'name'           => $world->name,
            'icon'           => $world->icon,
            'theme_color'    => $world->theme_color,
            'is_free'        => $document['world']['is_free'],
            'sort_order'     => (int) $world->sort_order,
            'mission_count'  => count($missions),
            'question_count' => array_sum(array_map(fn ($mission) => count($mission['questions']), $missions)),
            'media_count'    => count($this->media),
            'bytes_core'     => strlen($json) + $this->mediaBytes(core: true),
            'bytes_video'    => $this->mediaBytes(core: false),
            'sha256'         => hash('sha256', $json),
            'json_path'      => $path,
            'url'            => $this->resolver->publicUrl($path),
            'missing_media'  => array_values($this->missing),
            'published_at'   => now(),
        ]);

        Cache::forget('content:versions');
        Cache::forget('content:catalog');

        $this->pruneOldVersions($packId);

        return $pack;
    }

    /**
     * @return array<int,array>
     */
    protected function missions(AdventureWorld $world): array
    {
        $missions = Mission::where('adventure_world_id', $world->id)
            ->whereIn('status', ['published', 'in_review'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($missions->isEmpty()) {
            // A world whose missions were never marked published still has to be
            // playable in a beta build, so fall back to everything attached to it.
            $missions = Mission::where('adventure_world_id', $world->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        $levelCap = $this->levelCap($world);

        return $missions->map(function (Mission $mission) use ($levelCap) {
            $questions = $this->questionsFor($mission);
            $perSession = (int) ($mission->questions_per_session ?: config('kiddoquest.session.questions_default', 8));

            return [
                'id'                     => (int) $mission->id,
                'slug'                   => $mission->slug,
                'title'                  => $mission->title,
                'display_title'          => $mission->display_title,
                'description'            => $mission->description,
                'sort_order'             => (int) $mission->sort_order,
                'questions_per_session'  => max(1, min($perSession, $levelCap)),
                'pass_threshold_percent' => (int) ($mission->pass_threshold_percent ?: 60),
                'stars_reward'           => (int) ($mission->stars_reward ?: 3),
                'estimated_minutes'      => (int) ($mission->estimated_minutes ?: 5),
                'allow_replay'           => (bool) $mission->allow_replay,
                'intro'                  => [
                    'text'  => $mission->intro_narration_text,
                    'audio' => null,
                ],
                'outro' => [
                    'text'  => $mission->outro_narration_text,
                    'audio' => null,
                ],
                'video'     => $this->video($mission),
                'questions' => $questions,
            ];
        })->all();
    }

    /**
     * The whole bank travels with the mission; the app draws the session.
     *
     * @return array<int,array>
     */
    protected function questionsFor(Mission $mission): array
    {
        $bank = $mission->questionBank;

        if (! $bank) {
            return [];
        }

        $questions = $bank->assignedQuestions()->exists()
            ? $bank->assignedQuestions()->with(['options', 'quizType', 'narration'])->get()
            : $bank->questions()->with(['options', 'quizType', 'narration'])->get();

        return $questions->map(function (QuizQuestion $question) {
            $normalized = QuestionNormalizer::fromModel($question);

            $normalized['bank_id'] = (int) ($question->question_bank_id ?: $mission->question_bank_id ?? 0);
            $normalized['image'] = $this->registerMedia($normalized['image'], 'image');
            $normalized['audio'] = $this->registerMedia($normalized['audio'], 'audio');
            $normalized['narration']['audio'] = $this->registerMedia($normalized['narration']['audio'] ?? null, 'audio');

            $normalized['options'] = array_map(function (array $option) {
                $option['image'] = $this->registerMedia($option['image'], 'image');
                $option['audio'] = $this->registerMedia($option['audio'], 'audio');

                return $option;
            }, $normalized['options']);

            unset($normalized['type_name'], $normalized['type_icon']);

            return $normalized;
        })->all();
    }

    protected function video(Mission $mission): ?array
    {
        $url = $mission->video_url ?: ($mission->videoMedia->url ?? null);

        if (! $url) {
            return null;
        }

        $path = $this->registerMedia($url, 'video', required: false);

        if (! $path) {
            return null;
        }

        return [
            'path'     => $path,
            'optional' => true,
        ];
    }

    /**
     * Record a media reference once and hand back the stable key the app uses to
     * find the downloaded file. Returns null for an empty reference.
     */
    protected function registerMedia(?string $url, string $kind, bool $required = true): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $resolved = $this->resolver->resolve($url, $kind);

        if ($resolved === null) {
            $this->missing[] = ['url' => $url, 'kind' => $kind];

            return null;
        }

        $path = $resolved['path'];

        if (! isset($this->media[$path])) {
            $this->media[$path] = $resolved + ['required' => $required];
        } elseif ($required) {
            $this->media[$path]['required'] = true;
        }

        return $path;
    }

    protected function mediaBytes(bool $core): int
    {
        $total = 0;

        foreach ($this->media as $entry) {
            $isVideo = ($entry['kind'] ?? '') === 'video';

            if ($core === ! $isVideo) {
                $total += (int) ($entry['bytes'] ?? 0);
            }
        }

        return $total;
    }

    /**
     * pack ids read like pg-math-whispering-forest so a human can find them in
     * a storage bucket without a lookup table.
     */
    public function packId(AdventureWorld $world): string
    {
        $level = strtolower($this->levelCode($world) ?: 'all');
        $subject = strtolower($this->subjectCode($world) ?: 'general');

        return Str::slug("{$level}-{$subject}-{$world->slug}");
    }

    public function levelCode(AdventureWorld $world): ?string
    {
        return $world->subject?->level?->code;
    }

    public function subjectCode(AdventureWorld $world): string
    {
        return $world->subject?->code ?: strtoupper($world->subject_category);
    }

    protected function levelCap(AdventureWorld $world): int
    {
        $caps = config('kiddoquest.session.questions_per_level', []);
        $code = $this->levelCode($world);

        return (int) ($caps[$code] ?? config('kiddoquest.session.questions_default', 8));
    }

    /**
     * Keep a few old versions alive so a device that is mid-mission is never
     * left pointing at a file that no longer exists.
     */
    protected function pruneOldVersions(string $packId): void
    {
        $keep = max(1, (int) config('kiddoquest.content.keep_versions', 3));

        $stale = ContentPack::where('pack_id', $packId)
            ->orderByDesc('version')
            ->skip($keep)
            ->take(100)
            ->get();

        $disk = Storage::disk(config('kiddoquest.content.disk', 'public'));

        foreach ($stale as $pack) {
            $disk->deleteDirectory(dirname($pack->json_path));
            $pack->delete();
        }
    }
}
